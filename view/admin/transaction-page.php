<div class="wrap">
    <div class="manual-button">
        <form action="" method="post">
            <input type="submit" name="mf100-manual-transaction-checker" value="Recheck transactions" />
        </form>
    </div>
    <h2 class="nav-tab-wrapper" id="mf100-nav-tabs">
        <a href="#" id="link-unmatched" class="nav-tab">Unmatched</a>
        <a href="#" id="link-matched" class="nav-tab">Matched</a>
    </h2>

    <div class="year-wrapper">
        <?php $first = true; ?>
        <?php foreach ($transactions as $part => $partialTransactions) : ?>
            <?php $this->showTemplate('transaction-part', array('part' => $part, 'transactions' => $partialTransactions, 'first' => $first)); ?>
            <?php $first = false; ?>
        <?php endforeach; ?>
    </div>

    <div id="transaction-dialog-form" style="display: none;">
        <fieldset>
            <label for="user-id">User ID</label>
            <input type="text" name="user-id" value="" class="text ui-widget-content ui-corner-all" />
        </fieldset>
    </div>
</div>