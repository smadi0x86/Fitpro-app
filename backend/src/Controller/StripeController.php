<?php

namespace Smadi0x86wsl\Backend\Controller;

use OpenSwoole\Http\Request;
use OpenSwoole\Http\Response;
use Smadi0x86wsl\Backend\Database\DatabaseConnection;
require 'vendor/autoload.php';

class StripeController {
    public function createCheckoutSession(Request $request, Response $response) {
        \Stripe\Stripe::setApiKey('sk_test_51OU5pSKwKKX8toD9VDon2HlnCF99u0ddxG4sTXUeyYiTzF1qWHIObzLM8dp2NrbWs0HzkBRYK0qGDlPCbdNgUt9Z00rqe8Voqd');

        // Get raw body data and decode JSON
        $body = $request->getContent();
        $data = json_decode($body, true);
        $cart = $data['cart'];

        // Convert cart items to Stripe line items format
        $lineItems = [];
        foreach ($cart as $item) {
            // Check if 'name' key exists
            if (isset($item['name'])) {
                array_push($lineItems, [
                    'price_data' => [
                        'currency' => 'usd',
                        'product_data' => [
                            'name' => $item['name'],
                        ],
                        'unit_amount' => $item['price'] * 100,
                    ],
                    'quantity' => $item['quantity'],
                ]);
            }
        }

        try {
            // Create the session
            $session = \Stripe\Checkout\Session::create([
                'payment_method_types' => ['card'],
                'line_items' => $lineItems,
                'mode' => 'payment',
                'success_url' => 'https://fitpro.smadi0x86.me/payment-success',
                'cancel_url' => 'https://fitpro.smadi0x86.me/payment-failed',
                'metadata' => ['user_id' => $userId, 'membership_type' => $membershipType]
            ]);

            // Send response
            $response->header('Content-Type', 'application/json');
            $response->end(json_encode(['sessionId' => $session->id]));
        } catch (\Exception $e) {
            // Handle exceptions, such as invalid Stripe parameters
            $response->status(500);
            $response->end(json_encode(['error' => $e->getMessage()]));
        }
    }

    // public function createMembershipSession(Request $request, Response $response) {
    //     \Stripe\Stripe::setApiKey('sk_test_51OU5pSKwKKX8toD9VDon2HlnCF99u0ddxG4sTXUeyYiTzF1qWHIObzLM8dp2NrbWs0HzkBRYK0qGDlPCbdNgUt9Z00rqe8Voqd');

    //     $body = $request->getContent();
    //     $data = json_decode($body, true);
    //     $membershipType = $data['membershipType'];
    //     $price = $this->determinePriceBasedOnType($membershipType);

    //     try {
    //         $session = \Stripe\Checkout\Session::create([
    //             'payment_method_types' => ['card'],
    //             'line_items' => [[
    //                 'price_data' => [
    //                     'currency' => 'usd',
    //                     'product_data' => ['name' => $membershipType . ' Membership'],
    //                     'unit_amount' => $price * 100,
    //                 ],
    //                 'quantity' => 1,
    //             ]],
    //             'mode' => 'payment',
    //             'success_url' => 'https://fitpro.smadi0x86.me/payment-success',
    //             'cancel_url' => 'https://fitpro.smadi0x86.me/payment-failed',
    //         ]);

    //         $response->header('Content-Type', 'application/json');
    //         return $response->end(json_encode(['sessionId' => $session->id]));
    //     } catch (\Exception $e) {
    //         $response->status(500);
    //         return $response->end(json_encode(['error' => $e->getMessage()]));
    //     }
    // }


    // private function determinePriceBasedOnType($type) {

    // !    For stripe checkout, we need to determine the price based on the membership type

    //     switch ($type) {
    //         case 'Basic':
    //             return 4.99;
    //         case 'Associate':
    //             return 9.99;
    //         case 'Professional':
    //             return 19.99;
    //         default:
    //             return 0;
    //     }
    // }


    /**
     * Handles creation of a membership.
     *
     * @param Request $request The incoming HTTP request
     * @param Response $response The HTTP response to be sent
     */
    public function createMembership(Request $request, Response $response) {
            // \Stripe\Stripe::setApiKey('sk_test_51OU5pSKwKKX8toD9VDon2HlnCF99u0ddxG4sTXUeyYiTzF1qWHIObzLM8dp2NrbWs0HzkBRYK0qGDlPCbdNgUt9Z00rqe8Voqd');

            // Get raw body data and decode JSON
            $data = json_decode($request->getContent(), true);

            // Extract email and membership type from the request
            $email = $data['email'] ?? null;
            $membershipType = $data['membership_type'] ?? null;

            if (is_null($email) || is_null($membershipType)) {
                $response->status(400); // Bad Request
                return $response->end(json_encode(['error' => 'Missing email or membership_type']));
            }

            try {
                $db = DatabaseConnection::getInstance()->getConnection();

                // Fetch user by email
                $userQuery = $db->prepare("SELECT id FROM users WHERE email = ?");
                $userQuery->execute([$email]);
                $user = $userQuery->fetch();

                if (!$user) {
                    return $response->end(json_encode(['error' => 'User not found']));
                }

                $userId = $user['id'];
                $isPremium = true;
                $subscriptionEndDate = date('Y-m-d', strtotime('+1 month')); // 1 month subscription

                // Update user's membership details
                $sql = "UPDATE users SET is_premium = ?, subscription_type = ?, subscription_end_date = ? WHERE id = ?";
                $stmt = $db->prepare($sql);
                $stmt->execute([$isPremium, $membershipType, $subscriptionEndDate, $userId]);

                return $response->end(json_encode(['message' => 'Membership created successfully']));
            } catch (\PDOException $e) {
                $response->status(500);
                return $response->end(json_encode(['error' => 'Database error: ' . $e->getMessage()]));
            } catch (\Exception $e) {
                $response->status(500);
                return $response->end(json_encode(['error' => $e->getMessage()]));
            }
        }

    /**
     * Retrieves membership details.
     *
     * @param Request $request The incoming HTTP request
     * @param Response $response The HTTP response to be sent
     */
    public function getMembershipDetails(Request $request, Response $response) {
        $email = $request->get['email'] ?? null; // Assuming email is passed as a query parameter

        if (is_null($email)) {
            $response->status(400); // Bad Request
            return $response->end(json_encode(['error' => 'Email is required']));
        }

        try {
            $db = DatabaseConnection::getInstance()->getConnection();

            // Fetch membership details by email
            $stmt = $db->prepare("SELECT is_premium, subscription_type, subscription_end_date FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $membershipDetails = $stmt->fetch();

            if (!$membershipDetails) {
                return $response->end(json_encode(['error' => 'User not found']));
            }

            return $response->end(json_encode($membershipDetails));
        } catch (\PDOException $e) {
            $response->status(500);
            return $response->end(json_encode(['error' => 'Database error: ' . $e->getMessage()]));
        }
    }

    /**
     * Updates the membership details for a user.
     *
     * @param Request $request The incoming HTTP request
     * @param Response $response The HTTP response to be sent
     */
    public function updateMembership(Request $request, Response $response) {
        // Decode the JSON payload from the request
        $data = json_decode($request->getContent(), true);

        // Extract email and new membership details from the request
        $email = $data['email'] ?? null;
        $newMembershipType = $data['new_membership_type'] ?? null;
        $newSubscriptionEndDate = $data['new_subscription_end_date'] ?? null;

        if (is_null($email) || is_null($newMembershipType)) {
            $response->status(400); // Bad Request
            return $response->end(json_encode(['error' => 'Missing email or new_membership_type']));
        }

        try {
            $db = DatabaseConnection::getInstance()->getConnection();

            // Fetch user by email to get user ID
            $userQuery = $db->prepare("SELECT id FROM users WHERE email = ?");
            $userQuery->execute([$email]);
            $user = $userQuery->fetch();

            if (!$user) {
                return $response->end(json_encode(['error' => 'User not found']));
            }

            $userId = $user['id'];
            $isPremium = true; // Assuming any subscription change keeps the user as premium

            // Prepare SQL query for update
            $sql = "UPDATE users SET subscription_type = ?, subscription_end_date = ? WHERE id = ?";
            $params = [$newMembershipType, $newSubscriptionEndDate, $userId];
            $stmt = $db->prepare($sql);
            $stmt->execute($params);

            return $response->end(json_encode(['message' => 'Membership updated successfully']));
        } catch (\PDOException $e) {
            $response->status(500);
            return $response->end(json_encode(['error' => 'Database error: ' . $e->getMessage()]));
        }
    }

    /**
     * Deletes a user's membership.
     *
     * @param Request $request The incoming HTTP request
     * @param Response $response The HTTP response to be sent
     */
    public function deleteMembership(Request $request, Response $response) {
        $data = json_decode($request->getContent(), true);

        $email = $data['email'] ?? null;

        if (is_null($email)) {
            $response->status(400); // Bad Request
            return $response->end(json_encode(['error' => 'Email is required']));
        }

        try {
            $db = DatabaseConnection::getInstance()->getConnection();

            // Fetch user by email to get user ID
            $userQuery = $db->prepare("SELECT id FROM users WHERE email = ?");
            $userQuery->execute([$email]);
            $user = $userQuery->fetch();

            if (!$user) {
                return $response->end(json_encode(['error' => 'User not found']));
            }

            $userId = $user['id'];

            // Reset membership details
            $sql = "UPDATE users SET is_premium = false, subscription_type = NULL, subscription_end_date = NULL WHERE id = ?";
            $stmt = $db->prepare($sql);
            $stmt->execute([$userId]);

            return $response->end(json_encode(['message' => 'Membership deleted successfully']));
        } catch (\PDOException $e) {
            $response->status(500);
            return $response->end(json_encode(['error' => 'Database error: ' . $e->getMessage()]));
        }
    }
}
