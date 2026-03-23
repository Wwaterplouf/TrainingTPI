<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-12 col-md-8 col-lg-6">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h1 class="h4 mb-3">Entrez le nouveau mot de passe</h1>
                    <form method="post" action="/newuserpassword" class="vstack gap-3">
                        <div>
                            <label class="form-label">Nouveau mot de passe</label>
                            <input class="form-control" name="pwd" type="password" required>
                        </div>
                        <div>
                            <label class="form-label">Entrez à nouveau le nouveau mot de passe</label>
                            <input class="form-control" name="pwdConfirm" type="password" required>
                        </div>
                        <input type="hidden" name="idUser" value="<?= $idUser ?>">
                        <button class="btn btn-primary" type="submit">Envoyer</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>