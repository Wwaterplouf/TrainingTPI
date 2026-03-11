<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-12 col-md-8 col-lg-5">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-light border-0 py-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <h2 class="h4 mb-0">Connexion</h2>
                            <div class="text-muted small">Identifiez-vous pour accéder à l’application.</div>
                        </div>
                        <span class="badge bg-primary text-uppercase">Login</span>
                    </div>
                </div>

                <div class="card-body p-4">
                    <form method="post" action="/login">
                        <?php if (!empty($data['error'])): ?>
                            <div class="alert alert-danger">
                                <?= htmlspecialchars((string)$data['error'], ENT_QUOTES, 'UTF-8') ?>
                            </div>
                        <?php endif; ?>

                        <div class="mb-3">
                            <label for="username" class="form-label">Nom d'utilisateur</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-person"></i></span>
                                <input
                                    type="text"
                                    id="username"
                                    name="username"
                                    class="form-control"
                                    placeholder="Entrez votre nom d'utilisateur"
                                    value="<?= htmlspecialchars((string)($data['username'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"
                                    required
                                    autocomplete="username"
                                >
                            </div>
                            <div class="form-text">Ex. prenom.nom ou pseudo.</div>
                        </div>

                        <div class="mb-4">
                            <label for="password" class="form-label">Mot de passe</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-shield-lock"></i></span>
                                <input
                                    type="password"
                                    id="password"
                                    name="password"
                                    class="form-control"
                                    placeholder="Entrez votre mot de passe"
                                    required
                                    autocomplete="current-password"
                                >
                            </div>
                        </div>

                        <div class="d-flex justify-content-between align-items-center">
                            <a href="/" class="btn btn-outline-secondary">
                                <i class="bi bi-arrow-left"></i> Retour
                            </a>

                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-box-arrow-in-right me-1"></i>
                                Se connecter
                            </button>
                        </div>

                        <hr class="my-4">

                        <div class="text-center">
                            <a href="/users/register" class="text-decoration-none">
                                Créer un compte
                            </a>
                        </div>
                        <a href="/passwordlost">Mot de passe oublié ?</a>
                    </form>
                </div>

                <div class="card-footer bg-white border-0 pb-4"></div>
            </div>
        </div>
    </div>
</div>
