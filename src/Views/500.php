<div class="container text-center mt-5">
    <h1 class="display-4 text-danger">Erreur serveur</h1>

    <?php if (!empty($message)): ?>
        <h2 class="h4 mt-4">Message</h2>
        <pre class="text-start"><?= htmlspecialchars($message) ?></pre>
    <?php else: ?>
        <p>Une erreur est survenue.</p>
    <?php endif; ?>

    <?php if (!empty($debug) && $debug): ?>
        <?php if (!empty($file)): ?>
            <h2 class="h5 mt-4">Fichier</h2>
            <pre class="text-start">
<?= htmlspecialchars($file) ?> : ligne <?= htmlspecialchars((string) $line) ?>
            </pre>
        <?php endif; ?>

        <?php if (!empty($trace)): ?>
            <h2 class="h5 mt-4">Trace</h2>
            <pre class="text-start small"><?= htmlspecialchars($trace) ?></pre>
        <?php endif; ?>
    <?php endif; ?>

    <a href="/" class="btn btn-primary mt-4">Retourner à l'accueil</a>
</div>
