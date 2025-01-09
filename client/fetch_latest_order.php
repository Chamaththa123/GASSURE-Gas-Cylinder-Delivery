<?php
/// Include database configuration
require_once '../config/config.php';

header('Content-Type: application/json');

// Fetch the latest order ID and user details from the orders table
try {
    // Fetch the latest order details
    $order_query = $conn->prepare("
    SELECT o.id as orderId, o.order_date as order_date,
           u.first_name as first_name, 
           u.last_name as last_name, 
           u.email as email
    FROM orders o
    JOIN users u ON o.user_id = u.id
    ORDER BY o.id DESC 
    LIMIT 1
    ");
    $order_query->execute();
    $order_result = $order_query->get_result();

    // Check if there is any result
    if ($order_result->num_rows > 0) {
        $row = $order_result->fetch_assoc();
        
        // Send order details in response
        echo json_encode([
            'success' => true, 
            'order_id' => $row['orderId'],
            'order_date' => $row['order_date'],
            'user' => [
                'first_name' => $row['first_name'],
                'last_name' => $row['last_name'],
                'email' => $row['email']
            ]
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'No orders found.']);
    }

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error fetching the latest order.']);
}