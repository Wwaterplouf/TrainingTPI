<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-12 col-md-8 col-lg-6">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-light border-0 py-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <h2 class="h4 mb-0">
                                <?= htmlspecialchars($data['mode'], ENT_QUOTES, 'UTF-8') ?> un utilisateur
                            </h2>
                            <div class="text-muted small">
                                Renseignez les informations ci-dessous puis validez.
                            </div>
                        </div>
                        <span class="badge bg-<?= $data['mode'] === 'Ajouter' ? 'success' : 'warning' ?> text-uppercase">
                            <?= htmlspecialchars($data['mode'], ENT_QUOTES, 'UTF-8') ?>
                        </span>
                    </div>
                </div>

                <div class="card-body p-4">
                    <form method="post" action="/auth/register">
                        <?php if (!empty($data['errors'])): ?>
                            <div class="alert alert-danger">
                                <div class="fw-semibold mb-2">Veuillez corriger les erreurs suivantes :</div>
                                <ul class="mb-0">
                                    <?php foreach ($data['errors'] as $fieldErrors): ?>
                                        <?php foreach ($fieldErrors as $error): ?>
                                            <li><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></li>
                                        <?php endforeach; ?>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>

                        <?php if (($data['mode'] ?? '') === 'Modifier'): ?>
                            <input type="hidden" name="id" value="<?= (int) ($data['user']->id ?? 0) ?>">
                        <?php endif; ?>

                        <div class="mb-3">
                            <label for="username" class="form-label">Nom d'utilisateur</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-person"></i></span>
                                <input
                                    type="text"
                                    id="username"
                                    name="username"
                                    class="form-control<?= !empty($data['errors']['username']) ? ' is-invalid' : '' ?>"
                                    placeholder="Entrez un nom d'utilisateur"
                                    value="<?= htmlspecialchars($data['user']->username ?? '', ENT_QUOTES, 'UTF-8') ?>"
                                    required
                                >
                            </div>
                            <?php if (!empty($data['errors']['username'])): ?>
                                <div class="invalid-feedback d-block">
                                    <?= htmlspecialchars($data['errors']['username'][0], ENT_QUOTES, 'UTF-8') ?>
                                </div>
                            <?php else: ?>
                                <div class="form-text">Ex. prenom.nom ou pseudo (sans espaces).</div>
                            <?php endif; ?>
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">Mot de passe</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-shield-lock"></i></span>
                                <input
                                    type="password"
                                    id="password"
                                    name="password"
                                    class="form-control<?= !empty($data['errors']['password']) ? ' is-invalid' : '' ?>"
                                    placeholder="<?= ($data['mode'] ?? '') === 'Modifier'
                                        ? 'Laissez vide si inchangé (min. 8 caractères, 1 caractère spécial)'
                                        : 'Min. 8 caractères, 1 caractère spécial' ?>"
                                    <?= ($data['mode'] ?? '') === 'Modifier' ? '' : 'required' ?>
                                    autocomplete="<?= ($data['mode'] ?? '') === 'Modifier' ? 'new-password' : 'new-password' ?>"
                                >
                            </div>
                            <?php if (!empty($data['errors']['password'])): ?>
                                <div class="invalid-feedback d-block">
                                    <?= htmlspecialchars($data['errors']['password'][0], ENT_QUOTES, 'UTF-8') ?>
                                </div>
                            <?php else: ?>
                                <div class="form-text">
                                    Utilisez un mot de passe robuste (longueur + caractère spécial).
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label">Email de récupération</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-person"></i></span>
                                <input
                                    type="text"
                                    id="email"
                                    name="email"
                                    class="form-control<?= !empty($data['errors']['email']) ? ' is-invalid' : '' ?>"
                                    placeholder="Entrez un email auquel vous avez accès"
                                    value="<?= htmlspecialchars($data['user']->email ?? '', ENT_QUOTES, 'UTF-8') ?>"
                                    required
                                >
                            </div>
                            <?php if (!empty($data['errors']['email'])): ?>
                                <div class="invalid-feedback d-block">
                                    <?= htmlspecialchars($data['errors']['email'][0], ENT_QUOTES, 'UTF-8') ?>
                                </div>
                            <?php else: ?>
                                <div class="form-text">Obligatoire, pour la réinitialisation du mot de passe en cas de perte.</div>
                            <?php endif; ?>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-semibold">Rôles</label>
                            <div class="p-3 bg-light rounded">
                                <?php foreach ($data['allRoles'] as $role): ?>
                                    <div class="form-check">
                                        <input
                                            class="form-check-input"
                                            type="checkbox"
                                            name="roles[]"
                                            id="role_<?= $role->id ?>"
                                            value="<?= $role->id ?>"
                                            <?= (isset($data['selectedRoles']) && in_array($role->id, $data['selectedRoles'])) || ($data['user']->id && $data['user']->hasRole($role->name)) ? 'checked' : '' ?>
                                        >
                                        <label class="form-check-label" for="role_<?= $role->id ?>">
                                            <?= htmlspecialchars($role->name, ENT_QUOTES, 'UTF-8') ?>
                                            <?php if ($role->description): ?>
                                                <small class="text-muted">(<?= htmlspecialchars($role->description, ENT_QUOTES, 'UTF-8') ?>)</small>
                                            <?php endif; ?>
                                        </label>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between align-items-center">
                            <a href="/users" class="btn btn-outline-secondary">
                                <i class="bi bi-arrow-left"></i> Retour
                            </a>

                            <button type="submit" class="btn btn-<?= ($data['mode'] ?? '') === 'Ajouter' ? 'success' : 'warning' ?>">
                                <i class="bi bi-check2-circle me-1"></i>
                                <?= htmlspecialchars($data['mode'] ?? 'Valider', ENT_QUOTES, 'UTF-8') ?>
                            </button>
                        </div>
                    </form>
                </div>

                <div class="card-footer bg-white border-0 pb-4"></div>
            </div>
        </div>
    </div>
</div>
