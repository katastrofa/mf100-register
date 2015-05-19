<div class="mf100-transactions-table-wrap" id="transaction-<?php echo $part; ?>"<?php echo (!$first) ? ' style="display: none;"' : ''; ?>>
    <table class="wp-list-table widefat fixed users">
        <thead>
        <tr>
            <th scope="col" id="id" class="manage-column column-name sortable desc">Id</th>
            <th scope="col" id="user" class="manage-column column-name sortable desc">User</th>
            <th scope="col" id="date" class="manage-column column-email sortable desc">Date</th>
            <th scope="col" id="amount" class="manage-column column-email sortable desc">Amount</th>
            <th scope="col" id="mena" class="manage-column column-email sortable desc">Mena</th>
            <th scope="col" id="uzivatel" class="manage-column column-email sortable desc">Uzivatel</th>
            <th scope="col" id="komentar" class="manage-column column-email sortable desc">Komentar</th>
        </tr>
        </thead>
        <tfoot>
        <tr>
            <th scope="col" class="manage-column column-name sortable desc">Id</th>
            <th scope="col" class="manage-column column-name sortable desc">User</th>
            <th scope="col" class="manage-column column-email sortable desc">Date</th>
            <th scope="col" class="manage-column column-email sortable desc">Amount</th>
            <th scope="col" class="manage-column column-email sortable desc">Mena</th>
            <th scope="col" class="manage-column column-email sortable desc">Uzivatel</th>
            <th scope="col" class="manage-column column-email sortable desc">Komentar</th>
        </tr>
        </tfoot>

        <tbody id="the-list">
        <?php $alternate = false; ?>
        <?php foreach ($transactions as $transaction) : ?>
            <?php $alternate = $alternate xor true; ?>
            <?php $this->showTemplate('transaction-line', array('transaction' => $transaction, 'alternate' => $alternate)); ?>
        <?php endforeach; ?>
        </tbody>

    </table>
</div>