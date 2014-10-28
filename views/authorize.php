<p>
    <?php if ($user !== null): ?>
    Hi <strong><?= $user->getUsername() ?></strong>.
    <?php endif; ?>
    You have been sent here by <strong><?= $clientId ?></strong>.
</p>

<p>
    Click the button below to complete the authorize request and grant an <code>
    <?= $responseType === 'code' ? 'Authoriation Code' : 'Access Token' ?></code> to <?= $clientId ?>.
</p>

<div>
    <form method="post" action="<?= htmlspecialchars($formUrl) ?>">
        <input type="submit" value="Yes, I Authorize This Request" />
        <input type="hidden" name="authorize" value="1" />
    </form>
    <form method="post" action="<?= htmlspecialchars($formUrl) ?>">
        <input type="submit" value="Cancel" />
        <input type="hidden" name="authorize" value="0" />
    </form>
</div>
