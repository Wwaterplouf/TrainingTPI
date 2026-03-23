<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-12 col-md-8 col-lg-6">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h1 class="h4 mb-3">Réinitialisation du mot de passe</h1>
                    <form method="post" action="/sendmail" class="vstack gap-3">
                        <div>
                            <label class="form-label">Nom d'utilisateur</label>
                            <input class="form-control" name="username" type="text" required
                                value="<?= (string) ($username ?? '') ?>">
                        </div>
                        <div>
                            <label class="form-label">Email de récupération</label>
                            <input class="form-control" name="email" type="email" required
                                value="<?= (string) ($email ?? '') ?>">
                        </div>
                        <button class="btn btn-primary" type="submit">Envoyer</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>