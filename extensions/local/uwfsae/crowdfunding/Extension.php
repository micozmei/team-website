<?php

namespace Bolt\Extension\Uwfsae\Crowdfunding;

use Bolt\Application;
use Bolt\BaseExtension;

class Extension extends BaseExtension
{
    const OVERALL_GOAL = 25000;
    public static $REGISTRY_GOALS = array(
        'Aerodynamics' => 875,
        'Chassis' => 1500,
        'Electronics' => 1800,
        'Powertrain' => 1346,
        'Suspension' => 800,
        'Manufacturing' => 655,
        'Competition' => 10000,
    );

    public function initialize() {
        $this->addTwigFunction('crowdfunding_handler', 'twigCrowdfundingSubmit');
    }

    public function getName() {
        return 'Crowdfunding';
    }

    public function isSafe() {
        return true;
    }

    public function twigCrowdfundingSubmit() {
        $request = $this->app['request_stack']->getCurrentRequest();
        $is_post = ($request && $request->isMethod('POST'));

        if ($is_post) {
            return $this->handleSubmit($request);
        } else {
            return $this->generateData();
        }
    }

    private function getStripeSecretKey() {
        return $this->config['stripe-secret-key'];
    }

    private function getStripePublishableKey() {
        return $this->config['stripe-publishable-key'];
    }

    private function genPDO() {
        $cfg = $this->app['config'];
        $conn_params = array(
            'host' => $cfg->get('general/database/host'),
            'port' => $cfg->get('general/database/port'),
            'dbname' => $cfg->get('general/database/databasename'),
            'user' => $cfg->get('general/database/username'),
            'password' => $cfg->get('general/database/password'),
        );
        $pieces = array();
        foreach ($conn_params as $key => $value) {
            $pieces[] = $key.'='.$value;
        }
        $conn_str = 'pgsql:'.implode(';', $pieces);
        $dbh = new \PDO($conn_str);
        $create_table = <<<SQL
CREATE TABLE IF NOT EXISTS crowdfunding_donations (
    id SERIAL,
    timestamp TIMESTAMP,
    full_name VARCHAR(100) NOT NULL,
    amount INTEGER NOT NULL,
    email VARCHAR(200) NOT NULL,
    registry VARCHAR(50),
    address VARCHAR(1000),
    tshirt VARCHAR(1000)
);
SQL;
        $res = $dbh->query($create_table);
        if ($res === FALSE) {
            print_r($dbh->errorInfo());
        } else {
            $res->closeCursor();
        }
        return $dbh;
    }

    private function generateData() {
        $dbh = $this->genPDO();

        $supporters_query = <<<SQL
SELECT COUNT(*) AS supporters, SUM(amount)/100 AS raised
FROM crowdfunding_donations
SQL;
        $res = $dbh->query($supporters_query);
        if ($res === FALSE) {
            print_r($dbh->errorInfo());
        }
        $row = $res->fetch();
        $supporters = $row['supporters'];
        $total_raised = $row['raised'];
        $res->closeCursor();

        $registry = array();

        $registry_query = <<<SQL
SELECT registry, SUM(amount)/100 AS raised
FROM crowdfunding_donations
WHERE registry IS NOT NULL
GROUP BY registry;
SQL;
        $res = $dbh->query($registry_query);
        foreach ($res as $row) {
            $name = $row['registry'];
            $raised = intval($row['raised']);
            $goal = self::$REGISTRY_GOALS[$name];
            $registry[$name] = array(
                'raised' => $raised,
                'goal' => $goal,
                'percentage' => $raised / $goal,
            );
        }
        foreach (self::$REGISTRY_GOALS as $name => $goal) {
            if (!array_key_exists($name, $registry)) {
                $registry[$name] = array(
                    'raised' => 0,
                    'goal' => $goal,
                    'percentage' => 0.0,
                );
            }
        }
        //$res->closeCursor();
        return array(
            'stripe_publishable_key' => $this->getStripePublishableKey(),
            'supporters' => $supporters,
            'raised' => $total_raised,
            'goal' => self::OVERALL_GOAL,
            'percentage' => $total_raised / self::OVERALL_GOAL,
            'registry' => $registry,
        );
        /*return array(
            'stripe-api-key' => $this->getStripePublishableKey(),
            'supporters' => 116,
            'raised' => 18324,
            'goal' => 25000,
            'percentage' => 18324.0 / 25000,
            'registry' => array(
                'Aerodynamics' => array(
                    raised => 100,
                    goal => 875,
                    percentage => 100.0 / 875,
                ),
                'Chassis' => array(
                    raised => 150,
                    goal => 1500,
                    percentage => 150.0 / 1500,
                ),
                'Electronics' => array(
                    raised => 1500,
                    goal => 1800,
                    percentage => 1500.0 / 1800.0,
                ),
                'Powertrain' => array(
                    raised => 1345,
                    goal => 1346,
                    percentage => 1345.0 / 1346,
                ),
                'Suspension' => array(
                    raised => 200,
                    goal => 800,
                    percentage => 200.0 / 800,
                ),
                'Manufacturing' => array(
                    raised => 150,
                    goal => 655,
                    percentage => 150.0 / 655,
                ),
                'Competition' => array(
                    raised => 1500,
                    goal => 10000,
                    percentage => 1500.0 / 10000,
                ),
            ),
        );*/
    }

    private function handleSubmit($request) {
        \Stripe\Stripe::setApiKey($this->getStripeSecretKey());

        $fullname = $request->get('fullname');
        $amount = $request->get('amount');
        $email = $request->get('email');
        $stripe_token = $request->get('stripe_token');
        $registry = $request->get('registry');
        $address = null;
        $tshirt = null;
        if ($amount >= 40000) {
            $address = $request->get('address1')."\n".
                $request->get('address2')."\n".
                $request->get('city').", ".$request->get('state')." ".$request->get('zipcode');
            $tshirt = $request->get('tshirt');
        }

        $pdh = $this->genPDO();

        try {
            $charge = \Stripe\Charge::create(array(
                'amount' => $amount,
                'currency' => 'usd',
                'description' => 'Donation to UW Formula Motorsports',
                'source' => $stripe_token,
            ));

            if (!$charge->paid) {
                throw new Exception('paid is false');
            }
        } catch (\Stripe\Error\Base $e) {
            error_log('[Crowdfunding] Stripe error: '.$e->getMessage());
            return array(
                'error' => 'Couldn\'t charge card. Please try again.',
            );
        } catch (Exception $e) {
            error_log('[Crowdfunding] General error: '.$e->getMessage());
            return array(
                'error' => 'Couldn\'t charge card. Please try again.',
            );
        }

        $insertSql = <<<SQL
INSERT INTO crowdfunding_donations (
    full_name,
    amount,
    email,
    registry,
    address,
    tshirt
) VALUES (
    :full_name,
    :amount,
    :email,
    :registry,
    :address,
    :tshirt
);
SQL;

        $stmt = $pdh->prepare($insertSql);
        $stmt->execute(array(
            'full_name' => $fullname,
            'amount' => $amount,
            'email' => $email,
            'registry' => ($registry ? $registry : null),
            'address' => $address,
            'tshirt' => $tshirt,
        ));

        return array(
            'ok' => 1,
        );
    }
}

?>
