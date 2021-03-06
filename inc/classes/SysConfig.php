<?php
/**
 * \Elabftw\Elabftw\SysConfig
 *
 * @author Nicolas CARPi <nicolas.carpi@curie.fr>
 * @copyright 2012 Nicolas CARPi
 * @see http://www.elabftw.net Official website
 * @license AGPL-3.0
 * @package elabftw
 */
namespace Elabftw\Elabftw;

/**
 * Deal with stuff from sysconfig.php
 */
class SysConfig
{
    /** Used to store the PDO object */
    private $pdo;

    /**
     * Just give me the Db object and I'm good to go
     *
     */
    public function __construct()
    {
        $this->pdo = Db::getConnection();
    }

    /**
     * Add a new team
     *
     * @param string $name The new name of the team
     * @return bool The results of the SQL queries
     */
    public function addTeam($name)
    {
        // add to the teams table
        $sql = 'INSERT INTO teams (team_name, link_name, link_href) VALUES (:team_name, :link_name, :link_href)';
        $req = $this->pdo->prepare($sql);
        $req->bindParam(':team_name', $name);
        $req->bindValue(':link_name', 'Documentation');
        $req->bindValue(':link_href', 'doc/_build/html/');
        $result1 = $req->execute();
        // grab the team ID
        $new_team_id = $this->pdo->lastInsertId();

        // now we need to insert a new default set of status for the newly created team
        $sql = "INSERT INTO status (team, name, color, is_default) VALUES
        (:team, 'Running', '0096ff', 1),
        (:team, 'Success', '00ac00', 0),
        (:team, 'Need to be redone', 'c0c0c0', 0),
        (:team, 'Fail', 'ff0000', 0);";
        $req = $this->pdo->prepare($sql);
        $req->bindValue(':team', $new_team_id);
        $result2 = $req->execute();

        // insert only one item type with editme name
        $sql = "INSERT INTO `items_types` (`team`, `name`, `bgcolor`, `template`) VALUES (:team, 'Edit me', '32a100', '<p>Go to the admin panel to edit/add more items types!</p>');";
        $req = $this->pdo->prepare($sql);
        $req->bindValue(':team', $new_team_id);
        $result3 = $req->execute();

        // now we need to insert a new default experiment template for the newly created team
        $sql = "INSERT INTO `experiments_templates` (`team`, `body`, `name`, `userid`) VALUES
        (:team, '<p><span style=\"font-size: 14pt;\"><strong>Goal :</strong></span></p>
        <p>&nbsp;</p>
        <p><span style=\"font-size: 14pt;\"><strong>Procedure :</strong></span></p>
        <p>&nbsp;</p>
        <p><span style=\"font-size: 14pt;\"><strong>Results :</strong></span></p><p>&nbsp;</p>', 'default', 0);";
        $req = $this->pdo->prepare($sql);
        $req->bindValue(':team', $new_team_id);
        $result4 = $req->execute();

        return $result1 && $result2 && $result3 && $result4;
    }

    /**
     * Edit the name of a team, called by ajax in app/quicksave.php
     *
     * @param int $id The id of the team
     * @param string $name The new name we want
     * @return bool true on success
     */
    public function editTeam($id, $name)
    {
        $name = filter_var($name, FILTER_SANITIZE_STRING);
        $sql = "UPDATE teams
            SET team_name = :name
            WHERE team_id = :id";
        $req = $this->pdo->prepare($sql);
        $req->bindParam(':name', $name);
        $req->bindParam(':id', $id);
        return $req->execute();

    }
}
