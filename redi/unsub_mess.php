<?php

	$unsubArr = array("We are sorry to see you leave!","You will be missed :(","You email is removed from our mailing list.","You are unsubscribed.","We hate Goodbyes, but if this is what you want :(","You have been unsubscribed from our mailing list.","It's not the same without you.","We will miss you 0X0X.","Do you want to leave us and go, seriuosly?","Your changes have been noted.","Bye Bye! You were a Rockstar.");
	
	$sub_val = rand(1,11);
	

?>


<html>
<body>

<div style="font-size: 32px;margin-top: 40px;text-align:center;width:100%;font-family:Trebuchet MS, Helvetica, sans-serif;"><?php echo $unsubArr[$sub_val]; ?></div>
</body>
</html>