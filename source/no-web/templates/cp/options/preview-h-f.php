<?php
if($template->type == "HTML")
{
echo $template->head;
?>
<center><img src="/images/misc/example-creative.jpg" alt="oooo yea" width="419" height="419" border="0" /></center>
<?php
echo $template->foot;
}
else
{
?>
<textarea rows="40" cols="70">
<?php
echo $template->head;
?>
Text creative will go here below is demo text


Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Mauris eros nunc, convallis rutrum, consectetuer ac, scelerisque ac, velit. Donec magna ante, adipiscing sed, commodo id, pretium a, ipsum. Aliquam nonummy, lacus sit amet fermentum dictum, ante felis ultrices enim, sed scelerisque nibh mi a enim. Praesent et ipsum sed metus pharetra laoreet. Sed id libero. Class aptent taciti sociosqu ad litora torquent per conubia nostra, per inceptos hymenaeos. Aenean in mauris non tortor luctus iaculis. Aenean dui. Phasellus elit velit, vehicula a, posuere ac, accumsan a, augue. Nullam eget sapien. Nulla neque ante, fermentum et, pharetra at, vulputate et, erat. Praesent vitae nibh. Duis dignissim, nibh sit amet accumsan tempus, velit neque tempor lorem, id tincidunt risus risus quis ipsum. Fusce eros. Sed aliquet est eu lectus. Sed sem massa, tincidunt sit amet, varius sit amet, tempor nec, odio. Donec bibendum, pede a pulvinar suscipit, magna neque pellentesque justo, eu fringilla mauris diam at ipsum. Ut nec eros. Curabitur feugiat convallis quam. Maecenas id lectus.

<?php
echo $template->foot;
?>
</textarea>
<?php
}
?>