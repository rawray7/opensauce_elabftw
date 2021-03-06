<?php
/**
 * inc/footer.php
 *
 * @author Nicolas CARPi <nicolas.carpi@curie.fr>
 * @copyright 2012 Nicolas CARPi
 * @see http://www.elabftw.net Official website
 * @license AGPL-3.0
 */
?>
<!-- TODOLIST -->
<div id='slide-panel'>
    <form id="todo-form">
        <input id="todo" type="text" />
        <input id="submit" type="submit" class='button' value="TODOfy">
    </form>
   <ul id="show-items"></ul>
    <br><br>
    <a class='button' href="#" onClick='showPanel()'>Close</a>
    <br><br>
    <a href="#" style='float:left' id="clear-all">Clear All</a>
</div>

<footer>

    <p class='footer_left'>
    <a href='https://twitter.com/materialsgirlco'>
    <img src='img/twitter.png' alt='twitter' title='Follow materialsGIRL on Twitter !'>
    </a>
     <a href='https://github.com/rawray7/opensauce_elabftw'>
    <img src='img/github.png' alt='github' title='Open Sauce eLabFTW on GitHub'>
    </a>
    <span>
    <?php
    if (isset($_SESSION['auth']) && $_SESSION['is_sysadmin'] === '1') {
        ?>
        <!-- SYSADMIN MENU -->
        <span class='strong'>
        <a href='sysconfig.php'><?php echo _('Sysadmin panel'); ?></a>
    <?php
    }
    if (isset($_SESSION['auth']) && $_SESSION['is_admin'] === '1') {
        echo "<a href='admin.php'>" . _('Admin panel');
        // show counter of unvalidated users
        $sql = "SELECT count(validated) FROM users WHERE validated = 0 AND team = :team";
        $req = $pdo->prepare($sql);
        $req->bindValue(':team', $_SESSION['team_id']);
        $req->execute();
        $unvalidated = $req->fetchColumn();
        if ($unvalidated > 0) {
            echo " <span class='badge'>" . $unvalidated . "</span>";
        }
        echo "</a>";
    }
    echo "</span></p><div class='footer_right'>";
    echo _('Powered by') . " <a href='http://www.elabftw.net'>eLabFTW</a><br>";
    ?>
    <?php echo _('Page generated in') . ' '; ?><span class='strong'><?php echo round((microtime(true) - $_SERVER["REQUEST_TIME_FLOAT"]), 5); ?> seconds</span></div>
</footer>

<!-- todolist -->
<script src="js/todolist.min.js"></script>
<!-- konami code and unicorns -->
<script src="js/cornify.min.js"></script>
<!-- advanced search div -->
<script>
$('#adv_search').hide();
$('#big_search_input').click(function() {
    $('#adv_search').show();
});
</script>
<?php
if (isset($_SESSION['auth'])) {
    // show TODOlist
    echo "<script>
    key('".$_SESSION['prefs']['shortcuts']['todo'] . "', function(){
        showPanel();
    });
    </script>";
}
?>
</body>
</html>
