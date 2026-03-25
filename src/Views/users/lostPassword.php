<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-12 col-md-8 col-lg-6">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h1 class="h4 mb-3">Réinitialisation du mot de passe</h1>
                    <form method="post" action="/sendmail" class="vstack gap-3">
                        <div class="mb-3">
                            <label for="username" class="form-label">Nom d'utilisateur</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-person"></i></span>
                                <input
                                    type="text"
                                    id="username"
                                    name="username"
                                    class="form-control<?= !empty($data['errors']['username']) ? ' is-invalid' : '' ?>"
                                    placeholder="Entrez votre nom d'utilisateur"
                                    value="<?= htmlspecialchars($data['username'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                                    required
                                >
                            </div>
                            <?php if (!empty($data['errors']['username'])): ?>
                                <div class="invalid-feedback d-block">
                                    <?= htmlspecialchars($data['errors']['username'][0], ENT_QUOTES, 'UTF-8') ?>
                                </div>
                            <?php else: ?>
                                <div class="form-text">Vous recevrez un email à l'adresse associée à votre compte</div>
                            <?php endif; ?>
                        </div>
                        <button class="btn btn-primary" type="submit">Envoyer</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>