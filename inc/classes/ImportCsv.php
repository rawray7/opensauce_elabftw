<?php
/**
 * \Elabftw\Elabftw\ImportCsv
 *
 * @author Nicolas CARPi <nicolas.carpi@curie.fr>
 * @copyright 2012 Nicolas CARPi
 * @see http://www.elabftw.net Official website
 * @license AGPL-3.0
 * @package elabftw
 */
namespace Elabftw\Elabftw;

use \Exception;

/**
 * Import items from a csv file.
 */
class ImportCsv extends Import
{
    /** pdo object */
    private $pdo;

    /** the category in which we do the import */
    private $itemType;

    /** number of items we got into the database */
    public $inserted = 0;

    /** our file handle */
    private $handle;

    /**
     * Assign item type
     *
     */
    public function __construct()
    {
        $this->pdo = Db::getConnection();

        $this->checkFileReadable();
        $this->checkMimeType();
        $this->itemType = $this->getTarget();
        $this->openFile();
        $this->readCsv();
    }

    /**
     * Open the file, as the name suggests
     *
     * @throws Exception
     */
    protected function openFile()
    {
        $this->handle = fopen($this->getFilePath(), 'r');
        if ($this->handle === false) {
            throw new Exception('Cannot open file!');
        }
    }

    /**
     * Do the work
     *
     */
    private function readCsv()
    {
        $row = 0;
        $column = array();
        // loop the lines
        while ($data = fgetcsv($this->handle, 0, ",")) {
            $num = count($data);
            // get the column names (first line)
            if ($row == 0) {
                for ($i = 0; $i < $num; $i++) {
                    $column[] = $data[$i];
                }
                $row++;
                continue;
            }
            $row++;

            $title = $data[0];
            $body = '';
            $j = 0;
            foreach ($data as $line) {
                $body .= "<p><strong>" . $column[$j] . " :</strong> " . $line . '</p>';
                $j++;
            }
            // clean the body
            $body = str_replace('<p><strong> :</strong> </p>', '', $body);

            // SQL for importing
            $sql = "INSERT INTO items(team, title, date, body, userid, type)
                VALUES(:team, :title, :date, :body, :userid, :type)";
            $req = $this->pdo->prepare($sql);
            $result = $req->execute(array(
                'team' => $_SESSION['team_id'],
                'title' => $title,
                'date' => kdate(),
                'body' => $body,
                'userid' => $_SESSION['userid'],
                'type' => $this->itemType
            ));
            if ($result) {
                $this->inserted++;
            } else {
                throw new Exception('Error in SQL query!');
            }
        }
    }

    /**
     * Close our open file
     *
     */
    public function __destruct()
    {
        fclose($this->handle);
    }
}
