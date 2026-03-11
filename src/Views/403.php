<div class="container text-center mt-5">
    <h1 class="display-4 text-danger">Accès interdit</h1>

    <?php if (!empty($message)): ?>
        <pre><?= htmlspecialchars($message) ?></pre>
    <?php else: ?>
        <p>Vous n'avez pas les droits nécessaires pour accéder à cette ressource.</p>
    <?php endif; ?>

    <a href="/" class="btn btn-primary mt-4">Retourner à l'accueil</a>
</div>
