<?php

print "Testing iFrames";
?>

<html>
<body>

<iframe height="100%" width="100%" src="//test.test.com" name="iframe_a"></iframe>

<p><a href="//test2.test.com" target="iframe_a">Netman</a></p>

<p>When the target of a link matches the name of an iframe, the link will open in the iframe.</p>

</body>
</html>