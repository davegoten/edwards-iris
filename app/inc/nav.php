<?php
namespace EdwardsEyes\inc;
?>
<div class="nav clearfix">
    <nav class="nav">
        <?php if (!empty($_SESSION['userinfo']['id'])) { ?>
        <a href="<?php echo ROOT_FOLDER; ?>/logout.php" class="button">Logout</a>
        <a href="<?php echo ROOT_FOLDER; ?>/admin/dashboard.php" class="button">Dashboard</a>
        <?php } else {?>
        <a href="<?php echo ROOT_FOLDER; ?>/login.php" class="button">Login</a>
        <?php }?>
        <?php if (!empty($_SESSION['userinfo']['access']) &&
            intval($_SESSION['userinfo']['access']) >= array_search('coordinator', ACL_RANKS)
        ) { ?>
            <a href="<?php echo ROOT_FOLDER; ?>/admin/studies" class="button">Manage Studies</a>
        <?php }?>
        <a href="<?php echo ROOT_FOLDER; ?>/instructions.php" class="button">Instructions</a>
    </nav>
</div><?php

