<?php
namespace EdwardsEyes\inc;

if (!empty($_SESSION['message'])) { ?>
    <ul class="errors">
<?php foreach ((array) $_SESSION['message'] as $msg) { ?>
        <li><?php echo $msg;?></li>
<?php }?>
    </ul>
<?php
unset($_SESSION['message']);
} ?>
