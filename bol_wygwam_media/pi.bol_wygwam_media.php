<?php

/**
 * BOL Wygwam Media Class
 *
 * @package     ExpressionEngine
 * @category    Plugin
 * @author      Bits of Love
 * @copyright   Copyright (c) 2012, Bits of Love
 * @link        http://www.bitsoflove.be
 */


$plugin_info       = array(
	'pi_name'        => 'BOL Wygwam Media',
	'pi_version'     => '0.1',
	'pi_author'      => 'Bits of Love',
	'pi_author_url'  => 'http://www.bitsoflove.be',
	'pi_description' => 'Automatically converts Wygwam tags to a gallery/slider or embedded youtube video',
	'pi_usage'       => Bol_wygwam_media::usage()
);

class Bol_wygwam_media
{
	private $data;
	private $entry_id;
	private $channel_id;
	private $image_size;

	function Bol_wygwam_media()
	{
		$this->EE =& get_instance();
		$TMPL = $this->EE->TMPL;

		$this->data = $TMPL->tagdata;
		$this->entry_id = ($TMPL->fetch_param('entry_id')) ? $TMPL->fetch_param('entry_id') : $TMPL->tagdata;
		$this->channel_id = $this->get_channel_id_from_param($this->entry_id);

		$this->image_size = ($TMPL->fetch_param('image_size')) ? $TMPL->fetch_param('image_size') : 'medium';

		$slider_ul_id = ($TMPL->fetch_param('slider_ul_id')) ? $TMPL->fetch_param('slider_ul_id') : NULL;
		$slider_ul_class = ($TMPL->fetch_param('slider_ul_class')) ? $TMPL->fetch_param('slider_ul_class') : NULL;
		$slider_container_id = ($TMPL->fetch_param('slider_container_id')) ? $TMPL->fetch_param('slider_container_id') : NULL;
		$slider_container_class = ($TMPL->fetch_param('slider_container_class')) ? $TMPL->fetch_param('slider_container_class') : NULL;

		$gallery_ul_id = ($TMPL->fetch_param('gallery_ul_id')) ? $TMPL->fetch_param('gallery_ul_id') : NULL;
		$gallery_ul_class = ($TMPL->fetch_param('gallery_ul_class')) ? $TMPL->fetch_param('gallery_ul_class') : NULL;
		$gallery_container_id = ($TMPL->fetch_param('gallery_container_id')) ? $TMPL->fetch_param('gallery_container_id') : NULL;
		$gallery_container_class = ($TMPL->fetch_param('gallery_container_class')) ? $TMPL->fetch_param('gallery_container_class') : NULL;

		$params = array(
			"slider_ul_id"				=> $slider_ul_id,
			"slider_ul_class"			=> $slider_ul_class,
			"slider_container_id"		=> $slider_container_id,
			"slider_container_class"	=> $slider_container_class,
			"gallery_ul_id"				=> $gallery_ul_id,
			"gallery_ul_class"			=> $gallery_ul_class,
			"gallery_container_id"		=> $gallery_container_id,
			"gallery_container_class"	=> $gallery_container_class
		);
		
		$this->return_data = $this->perform_find_replace($params);
	}

	function get_channel_id_from_param($entry_id)
	{
		$this->EE->db->select('channel_id');
		$this->EE->db->from('exp_channel_titles');
		$this->EE->db->where('entry_id', $entry_id);

		$query = $this->EE->db->get();

		if ($query->num_rows() == 0)
		{
			$this->EE->TMPL->log_item("BOL Wygwam Media: No data found. (Entry_ID:{$this->entry_id})");
			return $this->EE->TMPL->tagdata;
		}
		$result = $query->result();
		return $result[0]->channel_id;
	}

	function perform_find_replace($params)
	{
		// Search for Slider
		if (strpos($this->data, '{slider}') !== false)
		{
			// Set params
			$ul_id = ($params['slider_ul_id']) ? ' id="'.$params['slider_ul_id'].'"' : '';
			$ul_class = ($params['slider_ul_class']) ? ' class="'.$params['slider_ul_class'].'"' : '';
			$container_id = ($params['slider_container_id']) ? ' id="'.$params['slider_container_id'].'"' : '';
			$container_class = ($params['slider_container_class']) ? ' class="'.$params['slider_container_class'].'"' : '';
			
			// Replace tag
			$this->replace_tag('{slider}',$ul_id,$ul_class,$container_id,$container_class);
		}

		// Search for Gallery
		if (strpos($this->data, '{gallery}') !== false)
		{
			// Set params
			$ul_id = ($params['gallery_ul_id']) ? ' id="'.$params['gallery_ul_id'].'"' : '';
			$ul_class = ($params['gallery_ul_class']) ? ' class="'.$params['gallery_ul_class'].'"' : '';
			$container_id = ($params['gallery_container_id']) ? ' id="'.$params['gallery_container_id'].'"' : '';
			$container_class = ($params['gallery_container_class']) ? ' class="'.$params['gallery_container_class'].'"' : '';

			// Replace tag
			$this->replace_tag('{gallery}',$ul_id,$ul_class,$container_id,$container_class);
		}

		// Replace YouTube
		$find = "^{youtube:(http:\/\/)?(www\.)?youtu(be)?\.([a-z])+\/(watch(.*?)(\?|\&)v=)?(.*?)(&(.)*)?}^";
		$replace = '<iframe width="560" height="315" src="http://www.youtube.com/embed/$8" frameborder="0" allowfullscreen></iframe>';
		$this->data = preg_replace($find, $replace, $this->data);
		// http://regexlib.com/Search.aspx?k=youtube&c=-1&m=-1&ps=20&AspxAutoDetectCookieSupport=1

		return $this->data;
	}

	function replace_tag($find,$ul_id,$ul_class,$container_id,$container_class)
	{
		$replace = '<div'.$container_id.$container_class.'>';
		$replace .= '<ul style="list-style-type:none;"'.$ul_id.$ul_class.'>';
		$replace .= '{exp:channel_images:images channel_id="'.$this->channel_id.'" dynamic="no" entry_id="'.$this->entry_id.'"}';
		$replace .= '<li><img src="{image:url:'.$this->image_size.'}" width="{image:width:'.$this->image_size.'}" height="{image:height:'.$this->image_size.'}" title="{image:description}" alt="{image:description}" /></li>';
		$replace .= "{/exp:channel_images:images}";
		$replace .= "</ul></div>";
		
		$this->data = str_replace($find, $replace, $this->data);
	}

	// ----------------------------------------
	//  Plugin Usage
	// ----------------------------------------

	// This function describes how the plugin is used.
	//  Make sure and use output buffering

	function usage()
	{
		ob_start();
		?>

# BOL Wygwam Media

  Converts pre-defined tags found in Wygwam content. This allows users to place a image-gallery / image-slider / youtube-video wherever they want throughout the text.
  Its easily customizable by using the tag parameters.
  
    {exp:bol_wygwam_tags slider_container_id="myID" slider_ul_class="myClass"}
        {content}
    {/exp:bol_wygwam_tags}
  
  This outputs:
  
    <div id="myID">
        <ul class="myClass">
            <li><img src="" alt=""/></li>
            <li><img src="" alt=""/></li>
            ...
        </ul>
    </div>

## Wygwam Parameters

	{slider}

	{gallery}

	{youtube:LINK} (http://youtu.be/...)

## Template tag Parameters

### image_size="medium"
Set the image size (as defined in the Channel Images fieldtype)

### slider_container_id=""

Set the ID of the slider container.

### slider_container_class=""

Set the CSS class of the slider container.

### slider_ul_id=""

Set the ID of the slider unordered-list.

### slider_ul_class=""

Set the CSS class of the slider unordered-list.

### gallery_container_id=""

Set the ID of the gallery container.

### gallery_container_class=""

Set the CSS class of the gallery container.

### gallery_ul_id=""

Set the ID of the gallery unordered-list.

### gallery_ul_class=""

Set the CSS class of the slider unordered-list.

## Usage

### Steps
1.  Create eg. a news fieldgroup with a Channel Images fieldtype and a Wygwam fieldtype
2.  Publish a news item and upload images to your Channel Images fieldtype
3.  Add the pre-defined tag inside Wygwam fieldtype

        Lorem ipsum dolor sit amet, consectetur adipiscing elit. Proin dictum nulla vitae leo aliquam rhoncus. 
        Suspendisse ac mauris turpis. Donec commodo lacus at eros luctus accumsan congue leo consectetur.
        
        {slider}
        
        Etiam mi leo, pretium a cursus vitae, faucibus sit amet elit. Praesent eu dolor non dui consequat vestibulum. 
        Class aptent taciti sociosqu ad litora torquent per conubia nostra, per inceptos himenaeos. 
        Vivamus sem odio, mattis in blandit vitae, dignissim nec elit.

## Example using [Flex Slider](http://www.woothemes.com/flexslider)

### Template Tag
    {exp:bol_wygwam_tags slider_container_class="flexslider" slider_ul_class="slides"}
        {content}
    {/exp:bol_wygwam_tags}

### Javascript
    if($('.flexslider').length)
    {
  		$('.flexslider').wrap('<div class="flex-nav-container"></div>');
  		$('.flexslider').flexslider({slideshowSpeed: 3000});
  	}


	
	<?php
		$buffer = ob_get_contents();

		ob_end_clean();

		return $buffer;
	}
}

?>