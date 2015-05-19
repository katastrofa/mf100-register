<div class="wrap">
    <div class="manual-button">
        <form action="" method="post">
            <input type="submit" name="mf100-manual-transaction-checker" value="yes" />
        </form>
    </div>
    <h2 class="nav-tab-wrapper" id="mf100-nav-tabs">
        <a href="#" id="year-link-unmatched" class="nav-tab">Unmatched</a>
        <a href="#" id="year-link-matched" class="nav-tab">Matched</a>
    </h2>

    <div class="year-wrapper">
        <?php $first = true; ?>
        <?php foreach ($transactions as $part => $partialTransactions) : ?>
            <?php $this->showTemplate('transaction-part', array('part' => $part, 'transactions' => $partialTransactions, 'first' => $first)); ?>
            <?php $first = false; ?>
        <?php endforeach; ?>
    </div>
</div>