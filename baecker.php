<?php declare(strict_types=1);
// UTF-8 marker äöüÄÖÜß€
/**
 * Class PageTemplate for the exercises of the EWA lecture
 * Demonstrates use of PHP including class and OO.
 * Implements Zend coding standards.
 * Generate documentation with Doxygen or phpdoc
 *
 * PHP Version 7.4
 *
 * @file     PageTemplate.php
 * @package  Page Templates
 * @author   Bernhard Kreling, <bernhard.kreling@h-da.de>
 * @author   Ralf Hahn, <ralf.hahn@h-da.de>
 * @version  3.1
 */

// to do: change name 'PageTemplate' throughout this file
require_once './Page.php';

/**
 * This is a template for top level classes, which represent
 * a complete web page and which are called directly by the user.
 * Usually there will only be a single instance of such a class.
 * The name of the template is supposed
 * to be replaced by the name of the specific HTML page e.g. baker.
 * The order of methods might correspond to the order of thinking
 * during implementation.
 * @author   Bernhard Kreling, <bernhard.kreling@h-da.de>
 * @author   Ralf Hahn, <ralf.hahn@h-da.de>
 */
class Baker extends Page
{
    // to do: declare reference variables for members 
    // representing substructures/blocks

    /**
     * Instantiates members (to be defined above).
     * Calls the constructor of the parent i.e. page class.
     * So, the database connection is established.
     * @throws Exception
     */
    protected function __construct()
    {
        parent::__construct();
        // to do: instantiate members representing substructures/blocks
    }

    /**
     * Cleans up whatever is needed.
     * Calls the destructor of the parent i.e. page class.
     * So, the database connection is closed.
     */
    public function __destruct()
    {
        parent::__destruct();
    }

    /**
     * Fetch all data that is necessary for later output.
     * Data is returned in an array e.g. as associative array.
	 * @return array An array containing the requested data. 
	 * This may be a normal array, an empty array or an associative array.
     */
    protected function getViewData():array
    {
        $query = "SELECT oa.ordered_article_id, oa.ordering_id, a.name, oa.status FROM ordered_article oa LEFT JOIN article a on oa.article_id = a.article_id WHERE oa.status <= 2";

        if($result = $this->_database->query($query)) {
            $dbArray = $result->fetch_all(MYSQLI_ASSOC);
            $result->free(); // Clear DB Record Set

            return $dbArray;
        }

        return array();
    }

    /**
     * First the required data is fetched and then the HTML is
     * assembled for output. i.e. the header is generated, the content
     * of the page ("view") is inserted and -if available- the content of
     * all views contained is generated.
     * Finally, the footer is added.
	 * @return void
     */
    protected function generateView():void
    {
        $data = $this->getViewData();
        $this->generatePageHeader('Bäcker', '', true); //to do: set optional parameters

        echo "<h1>Bäcker</h1>";

        if(count($data) <= 0) {
            echo "<p>Es gibt aktuell keine weiteren Bestellungen!</p>";
        }

        foreach($data as $v) {
            $oid = $v["ordering_id"];
            $id = $v["ordered_article_id"];
            $name = $v["name"];
            $status = $v["status"];

            $status == 0 ? $_checkedOrdered = 'checked' : $_checkedOrdered = '';
            $status == 1 ? $_checkedBaking = 'checked' : $_checkedBaking = '';
            $status == 2 ? $_checkedDone = 'checked' : $_checkedDone = '';

            echo <<<EOT
            <fieldset>
                <form action="#" method="POST" accept-charset="UTF-8">
                    <p>$name (Bestell Nr.: $id) [#$oid]</p>
                    <input type='radio' onchange='this.form.submit();' id='radioOrdered$id' name='status' value='0' $_checkedOrdered />
                    <label for='radioOrdered$id'>Bestellt</label>
                    <input type='radio' onchange='this.form.submit();' id='radioBaking$id' name='status' value='1' $_checkedBaking />
                    <label for='radioBaking$id'>Im Ofen</label>
                    <input type='radio' onchange='this.form.submit();' id='radioDone$id' name='status' value='2' $_checkedDone />
                    <label for='radioDone$id'>Fertig</label>
                    <input type='hidden' name='id' value='$id' />
                </form>
            </fieldset>

            <br />
            EOT;
        }

        $this->generatePageFooter();
    }

    /**
     * Processes the data that comes via GET or POST.
     * If this page is supposed to do something with submitted
     * data do it here.
	 * @return void
     */
    protected function processReceivedData():void
    {
        parent::processReceivedData();

        if(count($_POST)) {
            if(isset($_POST["status"]) and isset($_POST["id"])) {
                $status = $_POST["status"];
                $id = $_POST["id"];

                $stmt = $this->_database->prepare("UPDATE ordered_article SET status = (?) WHERE ordered_article_id = (?)");
                $stmt->bind_param("ii", $status, $id);
                $stmt->execute();
                //$query = "UPDATE ordered_article SET status = $status WHERE ordered_article_id = $id;";
                //$this->_database->query($query);

                header("HTTP/1.1 303 See Other");
                header("Location: baecker.php");
                die();
            }
        }
    }

    /**
     * This main-function has the only purpose to create an instance
     * of the class and to get all the things going.
     * I.e. the operations of the class are called to produce
     * the output of the HTML-file.
     * The name "main" is no keyword for php. It is just used to
     * indicate that function as the central starting point.
     * To make it simpler this is a static function. That is you can simply
     * call it without first creating an instance of the class.
	 * @return void
     */
    public static function main():void
    {
        try {
            $page = new Baker();
            $page->processReceivedData();
            $page->generateView();
        } catch (Exception $e) {
            //header("Content-type: text/plain; charset=UTF-8");
            header("Content-type: text/html; charset=UTF-8");
            echo $e->getMessage();
        }
    }
}

// This call is starting the creation of the page. 
// That is input is processed and output is created.
Baker::main();

// Zend standard does not like closing php-tag!
// PHP doesn't require the closing tag (it is assumed when the file ends). 
// Not specifying the closing ? >  helps to prevent accidents 
// like additional whitespace which will cause session 
// initialization to fail ("headers already sent"). 
//? >