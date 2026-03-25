<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-12 col-md-8 col-lg-6">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h1 class="h4 mb-3">Réinitialisation de votre mot de passe</h1>
                    <form method="post" action="/users/resetpassword" class="vstack gap-3">
                        <div class="mb-3">
                            <label for="password" class="form-label">Mot de passe</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-shield-lock"></i></span>
                                <input
                                    type="password"
                                    id="pwd"
                                    name="pwd"
                                    class="form-control<?= !empty($data['errors']['pwd']) ? ' is-invalid' : '' ?>"
                                    placeholder="Entrez le nouveau mot de passe"
                                    required
                                >
                            </div>
                            <?php if (!empty($data['errors']['pwd'])): ?>
                                <div class="invalid-feedback d-block">
                                    <?= htmlspecialchars($data['errors']['pwd'][0], ENT_QUOTES, 'UTF-8') ?>
                                </div>
                            <?php else: ?>
                                <div class="form-text">
                                    Utilisez un mot de passe robuste (minimum 8 de longueur + caractère spécial).
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="mb-3">
                            <label for="confirmPassword" class="form-label">Confirmer le mot de passe</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-shield-lock"></i></span>
                                <input
                                    type="password"
                                    id="pwdConfirm"
                                    name="pwdConfirm"
                                    class="form-control<?= !empty($data['errors']['pwdConfirm']) ? ' is-invalid' : '' ?>"
                                    placeholder="Entrez à nouveau le mot de passe"
                                    required
                                >
                            </div>
                            <?php if (!empty($data['errors']['pwdConfirm'])): ?>
                                <div class="invalid-feedback d-block">
                                    <?= htmlspecialchars($data['errors']['pwdConfirm'][0], ENT_QUOTES, 'UTF-8') ?>
                                </div>
                            <?php else: ?>
                                <div class="form-text">
                                    Les mots de passe doivent être identiques.
                                </div>
                            <?php endif; ?>
                        </div>
                        <input type="hidden" name="idUser" value="<?= $idUser ?>">
                        <button class="btn btn-primary" type="submit">Envoyer</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>