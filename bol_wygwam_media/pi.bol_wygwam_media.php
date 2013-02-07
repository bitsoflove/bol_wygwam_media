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

	function Bol_wygwam_media()
	{
		$this->EE =& get_instance();
		$TMPL = $this->EE->TMPL;

		$this->data = $TMPL->tagdata;
		$this->entry_id = ($TMPL->fetch_param('entry_id')) ? $TMPL->fetch_param('entry_id') : $TMPL->tagdata;
		$this->channel_id = $this->get_channel_id_from_param($this->entry_id);

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
		$replace .= '<li><img src="{image:url:medium}" width="{image:width:medium}" height="{image:height:medium}" title="{image:description}" alt="{image:description}" /></li>';
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

	This plugin will automatically convert Wygwam tags to a gallery/slider or embedded youtube video.

	Bits Of Love - Wygwam Media
	===========================

	Slider:

		{slider}

	Gallery:

		{gallery}

	YouTube:

		{youtube:LINK} ( http://youtu.be/T7gQrwnp550 )


	Simple Example
	===========================

	{exp:bol_wygwam_media entry_id="{entry_id}"}
		{content}
	{/exp:bol_wygwam_media}

	OUTPUT:
	<div>
		<ul>
			<li><img src=""/></li>
		</ul>
	</div>

	
	<?php
		$buffer = ob_get_contents();

		ob_end_clean();

		return $buffer;
	}
}

?>