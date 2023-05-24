<?php
$site_name = elgg_get_site_entity()->name;
$action = elgg_get_site_url() ."videos/youtube";
$logo = '<img src="'.elgg_get_site_url().'mod/mrdave_aalborg/graphics/icon57-SFN-V1.png" alt="Videos on '. $site_name . ' " height="36" width="36" align="left">';
$ulogo = '<img src="'.elgg_get_site_url().'mod/mrdave_aalborg/graphics/youtube.jpg" alt="Youtube Logo" height="36" width="36" align="right">';

$htmlBody = <<<END
<a href="/videos/all" title="Watch videos on $site_name "> $logo </a>                 
<a href="https://www.youtube.com" title="Powered by Youtube"> $ulogo </a>
<br>
<form action="$action" method="GET">
  <div>
    <b><center>Search Videos:</center></b><br> <input type="search" id="q" name="q" placeholder="Search Videos">
  </div>
  <div>
    Results (max 10): <input type="number" id="maxResults" name="maxResults" min="1" max="10" step="1" value="5">
    <br><br>
   </div>
  <input type="submit" value="Search" class="elgg-button elgg-button-submit">
  <br><br>
</form>
END;

echo $htmlBody;
