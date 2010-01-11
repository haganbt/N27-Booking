<?
// Member Footer Include Variables

//if navigation is hidden no table is left open to indent
//the page so check if we need to cloas this table or not
if ( $hide_navigation !== true ) {
?>
</td></tr></table>
<?php
}

if ( $hide_footer !== true ) {
?>
<div align="center" class="SiteVersion">
  <div align="center" class="DoNotPrint">Powered by <a href="http://www.n27.co.uk">Network27</a> - v2.3.2</div>
</div>
<?php
}
?>
<!-- end MainContentContainer -->
</div>
</body>
</html>