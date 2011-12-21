<?
	include("config.php");
	include("lib/functions.php");

        mysql_connect($host, $user, $pass) or die(mysql_error());
        mysql_select_db($table) or die(mysql_error());

	$start = head();

        if ($_POST['update'] == "yes") {
		foreach ($_POST as $key => $value) {
		$$key = $value;
                mysql_query("UPDATE config SET config = '$value' where setting = '$key'");
		}
                echo '<b>Updated Settings Correctly</b>';
        }

	$settings = getsettings();
?>

<body><div id="container">
<form method="post" class="niceform">
        <fieldset>
        <legend>Layout</legend>
        <dl>
                <dt><label for="showrestarts">Show Restarts</label></dt>
            <dd>
                    <select size="1" name="showrestarts" id="showrestarts">
                    <? if ($settings['showrestarts']['config'] == 'yes') { 
			echo '<option value="yes" selected="selected">Yes</option>';
			echo '<option value="no">No</option>';}
                    else {
			echo '<option value="no" selected="selected">No</option>';
			echo '<option value="yes">Yes</option>';
                    }?>
                </select><span class="hint">Weither or not to show the extra tab for server restarts. If you dont use this function set it to no.<span class="hint-pointer">&nbsp;</span>
            </dd>
        </dl>
        <dl>
                <dt><label for="showrules">Show Rules</label></dt>
            <dd>
                    <select size="1" name="showrules" id="showrules">
                    <? if ($settings['showrules']['config'] == 'yes') {
                        echo '<option value="yes" selected="selected">Yes</option>';
                        echo '<option value="no">No</option>';}
                    else {
                        echo '<option value="no" selected="selected">No</option>';
                        echo '<option value="yes">Yes</option>';
                    }?>
                </select><span class="hint">Weither or not to show the extra tab for rules. If you dont use this function set it to no.<span class="hint-pointer">&nbsp;</span>
            </dd>
        </dl>
        <dl>
                <dt><label for="adminactivity">View Admin Activity</label></dt>
            <dd>
                    <select size="1" name="adminactivity" id="adminactivity">
                    <? if ($settings['adminactivity']['config'] == 'yes') {
                        echo '<option value="yes" selected="selected">Yes</option>';
                        echo '<option value="no">No</option>';}
                    else {
                        echo '<option value="no" selected="selected">No</option>';
                        echo '<option value="yes">Yes</option>';
                    }?>
                </select><span class="hint">Weither or not to show the extra tab for admin activitylogs. If you dont use this function set it to no.<span class="hint-pointer">&nbsp;</span>
            </dd>
        </dl>
        </legend>
        </fieldset>
        <fieldset>
        <legend>Default Settings</legend>
        <dl>
                <dt><label for="defaultmotd">Default MOTD Value</label></dt>
            <dd>
                    <select size="1" name="defaultmotd" id="defaultmotd">
                    <? if ($settings['defaultmotd']['config'] == 'yes') {
                        echo '<option value="yes" selected="selected">Yes</option>';
                        echo '<option value="no">No</option>';}
                    else {
                        echo '<option value="no" selected="selected">No</option>';
                        echo '<option value="yes">Yes</option>';
                    }?>
                </select><span class="hint">If you add a new server, should it by default have the motd flag on? This means that if you use themotd page that comes with SSMS it will show in there, handy if you dont want your warservers or test servers in the motd.<span class="hint-pointer">&nbsp;</span>
            </dd>
        </dl>
        <dl>
                <dt><label for="defaultautoupdate">Default Auto Update</label></dt>
            <dd>
                    <select size="1" name="defaultautoupdate" id="defaultautoupdate">
                    <? if ($settings['defaultautoupdate']['config'] == 'yes') {
                        echo '<option value="yes" selected="selected">Yes</option>';
                        echo '<option value="no">No</option>';}
                    else {
                        echo '<option value="no" selected="selected">No</option>';
                        echo '<option value="yes">Yes</option>';
                    }?>
                </select><span class="hint">Default set to yes, will mean that if a update comes out for this type of game, it will initiate aquit to the server in order for it to automaticly update (assuming you have -autoupdate or some other form that tries to update when you restart your server) Be warned though, if your setup doesn't update automaticly it will restart 24/7<span class="hint-pointer">&nbsp;</span>
            </dd>
        </dl>
        <dl>
                <dt><label for="announceupdate">Update Announce</label></dt>
            <dd>
                    <select size="1" name="announceupdate" id="announceupdate">
                    <? if ($settings['announceupdate']['config'] == 'yes') {
                        echo '<option value="yes" selected="selected">Yes</option>';
                        echo '<option value="no">No</option>';}
                    else {
                        echo '<option value="no" selected="selected">No</option>';
                        echo '<option value="yes">Yes</option>';
                    }?>
                </select><span class="hint">Should we send out a message to the configured notification services if a update comes out?<span class="hint-pointer">&nbsp;</span>
            </dd>
        </dl>
        <dl>
                <dt><label for="announceserverdown">Announce Server Down</label></dt>
            <dd>
                    <select size="1" name="announceserverdown" id="announceserverdown">
                    <? if ($settings['announceserverdown']['config'] == 'yes') {
                        echo '<option value="yes" selected="selected">Yes</option>';
                        echo '<option value="no">No</option>';}
                    else {
                        echo '<option value="no" selected="selected">No</option>';
                        echo '<option value="yes">Yes</option>';
                    }?>
                </select><span class="hint">Should we send out a message to the configured notification services if a server goes down and up?<span class="hint-pointer">&nbsp;</span>
            </dd>
        </dl>
        <dl>
                <dt><label for="retrycount">Retry Count</label></dt>
            <dd><input type="retrycount" name="retrycount" id="retrycount" size="3" maxlength="3" value="<? echo $settings['retrycount']['config'];?>" /><span class="hint">Number of retry's the server can have before it sends out a notification that the server is down. Dont change this lower then 3 since a server could be changing maps and wont respond (and that will increase the retry count) thats also why in the server tab it will show as amber.<span class="hint-pointer"></span></dd>
        </dl>
        <dl>
                <dt><label for="defaultannounce">Default Announce</label></dt>
            <dd><input type="defaultannounce" name="defaultannounce" id="defaultannounce" size="30" maxlength="255" value="<? echo $settings['defaultannounce']['config'];?>" /><span class="hint">if a s                     erver is allowed to do a update, this is the default string a new server will get. It will send out this string to the server if a update is st                     arting. This is the be more userfriendly and inform the users of what is happening.<span class="hint-pointer">&nbsp;</span></dd>
        </dl>
        <dl>
                <dt><label for="server_prefix">Server Prefix</label></dt>
            <dd><input type="server_prefix" name="server_prefix" id="server_prefix" size="30" maxlength="255" value="<? echo $settings['server_prefix']['config'];?>" /><span class="hint">Server prefi                     x (to cut down the name in serveral places).<span class="hint-pointer">&nbsp;</span></dd>
        </dl>
        <dl>
                <dt><label for="netconrestart">Netcon Restart</label></dt>
            <dd><input type="netconrestart" name="netconrestart" id="netconrestart" size="30" maxlength="255" value="<? echo $settings['netconrestart']['config'];?>" /><span class="hint">The command                      that is send towards servers that use netcon (forks for l4d/l4d2 have this) preffered is 'shutdown' since it will wait for the last players to                      go. Ofcourse 'quit' can also be used.<span class="hint-pointer">&nbsp;</span></dd>
        </dl>

    </fieldset>
    <fieldset>
	<legend>Statistics</legend>
	<img src=images/stats.png>
        <dl>
                <dt><label for="usestats">Use Statistics?</label></dt>
            <dd>
                    <select size="1" name="usestats" id="usestats">
                    <? if ($settings['usestats']['config'] == 'yes') {
                        echo '<option value="yes" selected="selected">Yes</option>';
                        echo '<option value="no">No</option>';}
                    else {
                        echo '<option value="no" selected="selected">No</option>';
                        echo '<option value="yes">Yes</option>';
                    }?>
                </select><span class="hint">Use links to Statistical pages suchs as Hlstats in certain overviews?<span class="hint-pointer">&nbsp;</span>
            </dd>
        </dl>
        <dl>
                <dt><label for="statsprogram">Stats Program</label></dt>
            <dd>
                    <select size="1" name="statsprogram" id="usestats">
                    <? if ($settings['statsprogram']['config'] == 'hlxce') {
                        echo '<option value="hlxce" selected="selected">HlxCE</option>';
			echo '<option value="gameme">GameME</option>';}
		    else { 
                        echo '<option value="hlxce">HlxCE</option>';
                        echo '<option value="gameme" selected="selected">GameME</option>';
                    }?>
                </select><span class="hint">Which type of statistics program do you have? For now only HlxCE and GameME are supported<span class="hint-pointer">&nbsp;</span>
            </dd>
        </dl>
        <dl>
                <dt><label for="statsurl">Link to Stats Page</label></dt>
            <dd><input type="statsurl" name="statsurl" id="statsurl" size="30" maxlength="255" value="<? echo $settings['statsurl']['config'];?>" /><span class="hint">Url to the statistical page, just the root<span class="hint-pointer">&nbsp;</span></dd>
        </dl>
    </fieldset>

    <fieldset>
        <legend>Notifications</legend>
        <fieldset>
                <legend>E-Mail</legend>
                <img src=images/email.png>
        <dl>
                <dt><label for="useemail">Announce via E-Mail</label></dt>
            <dd>
                    <select size="1" name="useemail" id="useemail">
                    <? if ($settings['useemail']['config'] == 'yes') {
                        echo '<option value="yes" selected="selected">Yes</option>';
                        echo '<option value="no">No</option>';}
                    else {
                        echo '<option value="no" selected="selected">No</option>';
                        echo '<option value="yes">Yes</option>';
                    }?>
                </select><span class="hint">Do we want to be informed via email?<span class="hint-pointer">&nbsp;</span>
            </dd>
        </dl>
        <dl>
                <dt><label for="emailalert">Email Address</label></dt>
            <dd><input type="emailalert" name="emailalert" id="emailalert" size="30" maxlength="255" value="<? echo $settings['emailalert']['config'];?>" /><span class="hint">The E-mail to send th                     e messages for the alert to.<span class="hint-pointer">&nbsp;</span></dd>
        </dl>
        <dl>

        </legend>
        </fieldset>

        <fieldset>
                <legend>Growl</legend>
                <img src=images/growl.png>
        <dl>
                <dt><label for="usegrowl">Announce via Growl</label></dt>
            <dd>
                    <select size="1" name="usegrowl" id="usegrowl">
                    <? if ($settings['usegrowl']['config'] == 'yes') {
                        echo '<option value="yes" selected="selected">Yes</option>';
                        echo '<option value="no">No</option>';}
                    else {
                        echo '<option value="no" selected="selected">No</option>';
                        echo '<option value="yes">Yes</option>';
                    }?>
                </select><span class="hint">Do we want to be informed via Growl?<span class="hint-pointer">&nbsp;</span>
            </dd>
        </dl>
        <dl>
                <dt><label for="growlip">Growl Hostname</label></dt>
            <dd><input type="growlip" name="growlip" id="growlip" size="30" maxlength="255" value="<? echo $settings['growlip']['config'];?>" /><span class="hint">The Growl server hostname to u                     se. This can either be a IP or hostname ofcourse.<span class="hint-pointer">&nbsp;</span></dd>
        </dl>
        <dl>
                <dt><label for="growlpass">Growl Password</label></dt>
            <dd><input type="password" name="growlpass" id="growlpass" size="30" maxlength="255" value="<? echo $settings['growlpass']['config'];?>" /><span class="hint">Growl Password to use to                      authenticate, assuming you do have a password setup in growl.<span class="hint-pointer">&nbsp;</span></dd><dd>Click <a href=growl-init.php><b>                     here</b></a> to add PHP-Growl 'registration' (click again if gametypes changes)</dd>
        </dl>

        </legend>
        </fieldset>

        <fieldset>
                <legend>Twitter</legend>
                <img src=images/twitter.png>
        <dl>
                <dt><label for="usetwitter">Announce via Twitter</label></dt>
            <dd>
                    <select size="1" name="usetwitter" id="usetwitter">
                    <? if ($settings['usetwitter']['config'] == 'yes') {
                        echo '<option value="yes" selected="selected">Yes</option>';
                        echo '<option value="no">No</option>';}
                    else {
                        echo '<option value="no" selected="selected">No</option>';
                        echo '<option value="yes">Yes</option>';
                    }?>
                </select><span class="hint">Do we want to be informed via Twitter?<span class="hint-pointer">&nbsp;</span>
            </dd>
        </dl>
        <dl>
                <dt><label for="consumerkey">Consumer Key</label></dt>
            <dd><input type="password" name="consumerkey" id="consumerkey" size="30" maxlength="255" value="<? echo $settings['consumerkey']['config'];?>" /><span class="hint">Your consumer key                      found on the dev twitter site when you create a new app for it.<span class="hint-pointer">&nbsp;</span></dd>
        </dl>
        <dl>
                <dt><label for="consumersecret">Consumer Secret</label></dt>
            <dd><input type="password" name="consumersecret" id="consumersecret" size="30" maxlength="255" value="<? echo $settings['consumersecret']['config'];?>" /><span class="hint">Your cons                     umer secret found on the dev twitter site when you create a new app for it.<span class="hint-pointer">&nbsp;</span></dd>
        </dl>
        <dl>
                <dt><label for="OAuthToken">OAuthToken</label></dt>
            <dd><input type="password" name="OAuthToken" id="OAuthToken" size="30" maxlength="255" value="<? echo $settings['OAuthToken']['config'];?>" /><span class="hint">OAuthToken found with                     in your account by access tokens.<span class="hint-pointer">&nbsp;</span></dd>
        </dl>
        <dl>
                <dt><label for="OAuthTokenSecret">OAuthTokenSecret</label></dt>
            <dd><input type="password" name="OAuthTokenSecret" id="OAuthTokenSecret" size="30" maxlength="255" value="<? echo $settings['OAuthTokenSecret']['config'];?>" /><span class="hint">OAu                     thTokenSecret found within your account by access tokens.<span class="hint-pointer">&nbsp;</span></dd>
        </dl>

        </legend>
        </fieldset>



    </fieldset>
    <fieldset class="action">
	<input type="hidden" name="update" value="yes"><br>
        <input type="submit" name="submit" id="submit" value="Submit" />
    </fieldset>
</form>
</div></body>
</html>

<?	

        mysql_close();
        bottom( $start );

?>
