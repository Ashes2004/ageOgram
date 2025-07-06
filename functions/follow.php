<?php

function followUser($conn, $follower_id, $followed_id)
{
    // Check if already following
    $stmt = $conn->prepare("SELECT * FROM follows WHERE followed_id = ? AND follower_id = ?");
    $stmt->bind_param("ii", $followed_id, $follower_id);
    $stmt->execute();
    $record = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($record) {
        // Already following
        return false;
    }

    // Insert follow relationship
    $stmt = $conn->prepare("INSERT INTO follows (follower_id, followed_id) VALUES (?, ?)");
    $stmt->bind_param("ii", $follower_id, $followed_id);
    $success = $stmt->execute();
    $stmt->close();

    return true;
}

function unfollowUser($conn, $follower_id, $followed_id)
{
    // Delete the follow relationship
    $stmt = $conn->prepare("DELETE FROM follows WHERE follower_id = ? AND followed_id = ?");
    $stmt->bind_param("ii", $follower_id, $followed_id);
    $success = $stmt->execute();
    $stmt->close();

    return true;
}
?>
