<div class="row my-3">
    <?php
    if ($animes ?? []) {
        foreach ($animes as $anime) {

            $textStatus = '';
            if ($anime->endDate && $anime->status == "FINISHED")
            {
                $textStatus = 'Finished : ' . ucfirst(strtolower($anime->season)) . ' ' . ($anime->seasonYear);
            }
            else if ($anime->startDate && $anime->status == "RELEASING")
            {
                $textStatus = 'Releasing : Ep ' . $anime->episodes . ' (Started : ' . ($anime->startDate) . ')';
            }
            else {
                $textStatus = 'Announced for : ' . ucfirst(strtolower($anime->season)) . ' ' . ($anime->seasonYear);
            }

            $textAnimeGenres = '';
            for ($i = 0; $i < min(count($anime->genres), 3); $i++)
            {
                $textAnimeGenres .= '<span class="badge bg-secondary rounded-pill me-1">' . $anime->genres[$i] . '</span>';
            }

            
            echo '<div class="col-sm-2">';
            echo '  <div class="card mb-4 shadow m-3">'; // Ajout d'une ombre pour le style
            echo '      <div class="card-body">';
            echo '          <img class="card-img-top" src="' . ($anime->largeCover) . '" alt="Card image cap">';
            echo '          <h5 class="card-title">' . ($anime->englishTitle) . '</h5>';
            echo '          <p class="card-text">' . $textAnimeGenres . '</p>';
            echo '          <p class="card-text"><small class="text-muted">' . $textStatus . '</small></p>';
            // echo '          <p class="card-text"><small class="text-muted">Date: ' . ($anime->release_date) . '</small></p>';
            echo '          <div class="d-flex justify-content-between mt-3">';
            echo '              <a href="/list/addanime/' . ($anime->anilist_id) . '" class="btn btn-primary btn-sm" title="Add to list"><i class="bi bi-plus-circle"> Add to list</i></a>';
            echo '              <a href="/anime/view/' . ($anime->anilist_id) . '" class="btn btn-info btn-sm" title="Details"><i class="bi bi-info-circle"> Details</i></a>';
            echo '              <a href="/anime/like/' . ($anime->anilist_id) . '" class="btn btn-danger btn-sm" title="Like"><i class="bi bi-heart"></i> Like</i></a>';
            echo '          </div>';
            echo '      </div>';
            echo '  </div>';
            echo '</div>';
        }
        echo '<nav aria-label="pagination" class="">
                    <ul class="pagination justify-content-center">
                        <li class="page-item" ' . $pagination["previousPage"] . '>
                        <a class="page-link" href="/home/' . (int) $pagination["link1"] . '">Précédent</a>
                        </li>
                        <li class="page-item ' . ($pagination["active"] == 1 ? 'active' : '') . '"><a class="page-link" href="/home/' . $pagination["link1"] . '">' . $pagination["link1"] . '</a></li>
                        <li class="page-item ' . ($pagination["active"] == 2 ? 'active' : '') . '">
                        <a class="page-link" href="/home/' . $pagination["link2"] . '">' . $pagination["link2"] . '</a>
                        </li>
                        <li class="page-item ' . ($pagination["active"] == 3 ? 'active' : '') . '"><a class="page-link" href="/home/' . $pagination["link3"] . '">' . $pagination["link3"] . '</a></li>
                        <li class="page-item" ' . $pagination["nextPage"] . ' >
                        <a class="page-link" href="' . (int) $pagination["link3"] . '">Suivant</a>
                        </li>
                    </ul>
              </nav>';
    } else {
        echo 'aucun anime trouvé (vérifiez la connexion ou rendez vous sur le <a href="https://anilist.co/">site d\'Anilist</a>)!';
    }
    ?>
</div>