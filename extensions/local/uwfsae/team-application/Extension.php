<?php

namespace Bolt\Extension\Uwfsae\TeamApplication;

require __DIR__ . '/vendor/autoload.php';

use Bolt\Application;
use Bolt\BaseExtension;

class Extension extends BaseExtension
{
    private $client;
    private $driveService;

    public function initialize() {
        $this->addTwigFunction('team_application_handler', 'twigTeamApplicationSubmit');
    }

    public function getName() {
        return 'TeamApplication';
    }

    public function isSafe() {
        return true;
    }

    public function twigTeamApplicationSubmit() {
        $request = $this->app['request_stack']->getCurrentRequest();

        if ($request && $request->isMethod('POST')) {
            return $this->handleSubmit($request);
        } else {
            return array();
        }
    }

    private function handleSubmit($request) {
        // handle uploads
        $uploadPrefix = sprintf('%s %s', $request->get('first_name'), $request->get('last_name'));
        $folder = $this->createSupplementFolder($uploadPrefix);
        $resume = $this->uploadSupplement($folder->id, $_FILES['resume_file']['tmp_name'], $uploadPrefix . ' Resume');
        $transcript = $this->uploadSupplement($folder->id, $_FILES['transcript_file']['tmp_name'], $uploadPrefix . ' Transcript');
        $cover_letter = $this->uploadSupplement($folder->id, $_FILES['cover_letter_file']['tmp_name'], $uploadPrefix . ' Cover Letter');

        // add entry to spreadsheet
        // T27/T27_Admin Teams/T27_Technology_Management/Application Template
        $listfeed = $this->getSpreadsheetService()->getListFeed('1hqXCcqdfKxuHk4VM3HxKRDEQyg19nTMCjwlzOwrX13w');
        $listfeed->insert(array(
            'applicationtime' => date(DATE_RFC2822),
            'firstname' => $request->get('first_name'),
            'lastname' => $request->get('last_name'),
            'major' => $request->get('major'),
            'acceptedintodept' => $request->get('accepted_into_dept'),
            'email' => $request->get('email'),
            'phonenumber' => $request->get('phone_number'),
            'gradyear' => $request->get('graduation_year'),
            'sttechteam' => $request->get('1st_tech_team'),
            'ndtechteam' => $request->get('2nd_tech_team'),
            'techteamparagraph' => $request->get('tech_team_paragraph'),
            'stadmin' => $request->get('1st_admin_team'),
            'ndadmin' => $request->get('2nd_admin_team'),
            'adminteamparagraph' => $request->get('admin_team_paragraph'),
            'supplementalslink' => $folder->alternateLink
        ));

        return array("ok" => 1);
    }

    private function createSupplementFolder($folderName) {
        $folder = new \Google_Service_Drive_DriveFile();
        $folder->setTitle($folderName);
        $parent = new \Google_Service_Drive_ParentReference();
        $parent->setId('0B30QxxP--98YOUpqbDlLdi05WnM'); // T27/T27_Admin_Teams/T27_Technology_Management/Application Supplements
        $folder->setParents(array($parent));
        $folder->setMimeType('application/vnd.google-apps.folder');
        $folder = $this->getDriveService()->files->insert($folder);
        return $folder;
    }

    private function uploadSupplement($supplementFolderId, $tmpfile, $uploaded_name) {
        $file = new \Google_Service_Drive_DriveFile();
        $file->setTitle($uploaded_name);
        $parent = new \Google_Service_Drive_ParentReference();
        $parent->setId($supplementFolderId);
        $file->setParents(array($parent));
        $file = $this->getDriveService()->files->insert($file, array(
            'data' => file_get_contents($tmpfile),
            'mimeType' => 'application/pdf',
            'uploadType' => 'multipart'
        ));
        return $file;
    }

    private function getClient() {
        if (!$this->client) {
            $credentials = new \Google_Auth_AssertionCredentials(
                'application@uwfsae-1276.iam.gserviceaccount.com',
                array('https://www.googleapis.com/auth/drive', 'https://spreadsheets.google.com/feeds'),
                file_get_contents('/var/www/uwfsae-71f09e98c3c3.p12')
            );

            $this->client = new \Google_Client();
            $this->client->setApplicationName('application');
            $this->client->setClientId('105516445691427424805');
            $this->client->setAssertionCredentials($credentials);
            if ($this->client->getAuth()->isAccessTokenExpired()) {
                  $this->client->getAuth()->refreshTokenWithAssertion();
            }
            $accessToken = json_decode($this->client->getAccessToken())->access_token;
            // for spreadsheet API
            $serviceRequest = new \Google\Spreadsheet\DefaultServiceRequest($accessToken);
            \Google\Spreadsheet\ServiceRequestFactory::setInstance($serviceRequest);
        }
        return $this->client;

    }

    private function getDriveService() {
        if (!$this->driveService) {
            $this->driveService = new \Google_Service_Drive($this->getClient());
        }
        return $this->driveService;
    }

    private function getSpreadsheetService() {
        if (!$this->spreadsheetService) {
            $this->spreadsheetService = new \Google\Spreadsheet\SpreadsheetService();
        }
        return $this->spreadsheetService;
    }
}

?>
