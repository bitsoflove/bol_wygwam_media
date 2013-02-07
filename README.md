# Bits of Love - Wygwam Media

  Converts pre-defined tags found in WYGWAM content. This allows users to place a image-gallery / image-slider / youtube-video wherever they want throughout the text.
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

## WYGWAM Parameters

	{slider}

	{gallery}

	{youtube:LINK} (http://youtu.be/...)

## Template tag Parameters

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
1.  Upload Images to 'Channel Images' fieldtype
2.  Add the pre-defined tag inside the content

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
  		$('.flexslider .slides > li').css('display','none');
  		$('.flexslider').flexslider({slideshowSpeed: 3000});
  	}
