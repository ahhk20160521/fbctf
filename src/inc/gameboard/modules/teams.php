<?hh // strict

require_once($_SERVER['DOCUMENT_ROOT'] . '/../vendor/autoload.php');

/* HH_IGNORE_ERROR[1002] */
SessionUtils::sessionStart();
SessionUtils::enforceLogin();

class TeamModuleController {
  public async function genRender(): Awaitable<:xhp> {
    $leaderboard = await Team::genLeaderboard();
    $rank = 1;

    $list = <ul class="grid-list"></ul>;

    $gameboard = await Configuration::gen('gameboard');
    if ($gameboard->getValue() === '1') {
      foreach ($leaderboard as $leader) {
        $logo_name = $leader->getLogo();
        if (strpos($logo_name, "custom") === 0) {
          $logo_path = await $leader->getLogoPath();
          $image = <img class="icon--badge" src={$logo_path}></img>;
        } else {
          $iconbadge = '#icon--badge-' . $logo_name;
          $image = <svg class="icon--badge">
                     <use href={$iconbadge}/>
                   </svg>;
        }
        $list->appendChild(
          <li>
            <a href="#" data-team={$leader->getName()}>
              {$image}
            </a>
          </li>
        );
      }
    }

    return
      <div>
        <header class="module-header">
          <h6>Teams</h6>
        </header>
        <div class="module-content">
          <div class="fb-section-border">
            <!-- Removing the option for people to select their own team for now -->
            <!-- <div class="module-top">
              <div class="radio-tabs">
                <input type="radio" name="fb--module--teams" id="fb--module--teams--all" checked={true}/>
                <label for="fb--module--teams--all" class="click-effect"><span>Everyone</span></label>
                <input type="radio" name="fb--module--teams" id="fb--module--teams--your-team"/>
                <label for="fb--module--teams--your-team" class="click-effect"><span>Your Team</span></label>
              </div>
            </div> -->
            <div class="module-scrollable">
              {$list}
            </div>
          </div>
        </div>
      </div>;
  }
}

$teams_generated = new TeamModuleController();
echo \HH\Asio\join($teams_generated->genRender());
