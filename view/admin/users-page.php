<div class="wrap">
    <div class="mf100-fields">
        <?php $userOptions = Mf100UserOptions::getInstance(); ?>
        <?php foreach ($fields as $value) : ?>
            <?php $checked = ($userOptions->isFieldVisible($value)) ? 'checked="checked"' : ''; ?>
            <label for="mf100-<?php echo $value; ?>" style="padding-right: 15px;">
                <input type="checkbox" id="mf100-<?php echo $value; ?>" value="yes" <?php echo $checked; ?> />
                <?php echo $value; ?>
            </label>
        <?php endforeach; ?>
    </div>

    <h2 class="nav-tab-wrapper" id="mf100-nav-tabs">
        <?php foreach ($years as $year => $nothing) : ?>
            <a href="#" id="year-link-<?php echo $year; ?>" class="nav-tab"><?php echo $year; ?></a>
        <?php endforeach; ?>
    </h2>

    <div class="year-wrapper">
        <?php $first = true; ?>
        <?php foreach ($years as $year => $nothing) : ?>
            <?php $users = $this->getRegisteredUsers($year); ?>
            <?php $this->showTemplate('users-year', array('year' => $year, 'users' => $users, 'first' => $first, 'fields' => $fields)); ?>
            <?php $first = false; ?>
        <?php endforeach; ?>
    </div>

</div>