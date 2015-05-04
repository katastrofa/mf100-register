<div class="wrap">
    <h2 class="nav-tab-wrapper" id="mf100-nav-tabs">
        <?php foreach ($years as $year => $nothing) : ?>
            <a href="#" id="year-link-<?php echo $year; ?>" class="nav-tab"><?php echo $year; ?></a>
        <?php endforeach; ?>
    </h2>

    <div class="year-wrapper">
        <?php $first = true; ?>
        <?php foreach ($years as $year => $nothing) : ?>
            <?php $users = $this->getRegisteredUsers($year); ?>
            <?php $this->showTemplate('users-year', array('year' => $year, 'users' => $users, 'first' => $first)); ?>
            <?php $first = false; ?>
        <?php endforeach; ?>
    </div>

</div>