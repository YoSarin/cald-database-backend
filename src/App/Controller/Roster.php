<?php
namespace App\Controller;

use App\Exception\Http\Http404;
use App\Exception\Http\Http400;
use App\Model\PlayerAtRoster;

class Roster extends \App\Common
{
    public function create(\App\Request $request, $response, $args)
    {
        list($teamId, $tournamentBelongsToLeagueAndDivisionId) = $request->requireParams(['team_id', 'tournament_belongs_to_league_and_division_id']);
        $name = trim($request->getParam("name"));
        $roster = \App\Model\Roster::create($teamId, $tournamentBelongsToLeagueAndDivisionId, $name);
        $roster->save();

        return $this->container->view->render(
            $response,
            ['status' => 'OK', 'info' => 'Roster created', 'data' => $roster->getData()],
            200
        );
    }
    
    public function edit(\App\Request $request, $response, $args)
    {
        list($rosterId, $name) = $request->requireParams(['roster_id', 'name']);
        $roster = \App\Model\Roster::loadById($rosterId);
        if (!$roster) {
            throw new Http404("Wrong roster_id");
        }
        
        if ($roster->getFinalized()) {
            throw new Http400("Roster finalized");
        }
        
        $roster->setName($name);
        $roster->save();
        
        return $this->container->view->render(
            $response,
            ['status' => 'OK', 'info' => 'Roster updated', 'data' => $roster->getData()],
            200
        );
    }

    public function remove(\App\Request $request, $response, $args)
    {
        list($rosterId) = $request->requireParams(['roster_id']);
        $roster = \App\Model\Roster::loadById($rosterId);
        if (!$roster) {
            throw new Http404("Wrong roster_id");
        }
        
        if ($roster->getFinalized()) {
            throw new Http400("Roster finalized");
        }
        
        $roster->delete();
        return $this->container->view->render(
            $response,
            ['status' => 'OK', 'info' => 'Team roster deleted'],
            200
        );
    }

    public function addPlayer(\App\Request $request, $response, $args)
    {
        list($rosterId, $playerId) = $request->requireParams(['roster_id', 'player_id']);
        $roster = \App\Model\Roster::loadById($rosterId);
        if (!$roster) {
            throw new Http404("Wrong roster_id");
        }
        
        if ($roster->getFinalized()) {
            throw new Http400("Roster finalized");
        }

        if (PlayerAtRoster::exists(["AND" => ['player_id' => $playerId, 'roster_id' => $roster->getId()]])) {
            throw new Http400("Player is already on roster of this team");
        }
        
        $tournamentIds = array_map(function ($t) { return $t->getId(); }, $roster->getTournament());
        
        if (PlayerAtRoster::exists(
            ["AND" => ['player_id' => $playerId, 'tournament_id' => $tournamentIds]],
            [
                "[>]roster" => ["roster_id" => "id"],
                "[>]tournament_belongs_to_league_and_division" => ["roster.tournament_belongs_to_league_and_division_id" => "id"]
            ]
        )) {
            throw new Http400("Player is already on roster of another team");
        }
        
        $roster = PlayerAtRoster::create($playerId, $rosterId);
        $roster->save();

        return $this->container->view->render(
            $response,
            ['status' => 'OK', 'info' => 'Player added', 'data' => $roster->getData()],
            200
        );
    }
    
    public function finalize(\App\Request $request, $response, $args)
    {
        list($rosterId) = $request->requireParams(['roster_id']);
        $roster = \App\Model\Roster::loadById($rosterId);
        if (!$roster) {
            throw new Http404("Wrong roster_id");
        }
        
        $roster->setFinalized(true);
        $roster->save();
        
        return $this->container->view->render(
            $response,
            ['status' => 'OK', 'info' => 'Roster finalized', 'data' => $roster->getData()],
            200
        );
    }
    
    public function open(\App\Request $request, $response, $args)
    {
        list($rosterId) = $request->requireParams(['roster_id']);
        $roster = \App\Model\Roster::loadById($rosterId);
        if (!$roster) {
            throw new Http404("Wrong roster_id");
        }
        
        $roster->setFinalized(false);
        $roster->save();
        
        return $this->container->view->render(
            $response,
            ['status' => 'OK', 'info' => 'Roster unlocked', 'data' => $roster->getData()],
            200
        );
    }

    public function removePlayer(\App\Request $request, $response, $args)
    {
        list($rosterId, $playerId) = $request->requireParams(['roster_id', 'player_id']);
        $roster = \App\Model\Roster::loadById($rosterId);
        if (!$roster) {
            throw new Http404("Wrong roster_id");
        }
        
        if ($roster->getFinalized()) {
            throw new Http400("Roster finalized");
        }

        $playerRoster = PlayerAtRoster::load(['player_id' => $playerId, 'roster_id' => $rosterId]);
        if (empty($playerRoster)) {
            throw new Http400("Player is already on roster of another team");
        }

        foreach ($playerRoster as $roster) {
            $roster->delete();
        }

        return $this->container->view->render(
            $response,
            ['status' => 'OK', 'info' => 'Player removed from roster'],
            200
        );
    }
}
