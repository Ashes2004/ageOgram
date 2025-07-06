<?php



function getTrendingTagsByAgeCategory($conn, $user_id, $limit = 10) {
    // Step 1: Get current user's DOB
   
    $stmt = $conn->prepare("SELECT dob FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$user) return [];

    // Step 2: Determine age category
    $dob = new DateTime($user['dob']);
    $age = (new DateTime())->diff($dob)->y;

    if ($age >= 13 && $age <= 19) $user_category = 'Teen';
    elseif ($age >= 20 && $age <= 29) $user_category = 'Young Adult';
    elseif ($age >= 30 && $age <= 59) $user_category = 'Adult';
    else $user_category = 'Senior';

    // ✅ Step 3: Include users.dob in SELECT
    $stmt = $conn->prepare("SELECT posts.tags, users.dob FROM posts 
        JOIN users ON posts.user_id = users.id");
    $stmt->execute();
    $result = $stmt->get_result();

    $tag_counts = [];

    while ($row = $result->fetch_assoc()) {
        // Get poster's age
        if (!isset($row['dob'])) continue;

        $poster_dob = new DateTime($row['dob']);
        $poster_age = (new DateTime())->diff($poster_dob)->y;

        if ($poster_age >= 13 && $poster_age <= 19) $poster_category = 'Teen';
        elseif ($poster_age >= 20 && $poster_age <= 29) $poster_category = 'Young Adult';
        elseif ($poster_age >= 30 && $poster_age <= 59) $poster_category = 'Adult';
        else $poster_category = 'Senior';

        if ($poster_category !== $user_category) continue;

        // Count tags
        $tags = explode(',', $row['tags']);
        foreach ($tags as $tag) {
            $clean_tag = strtolower(trim($tag));
            if ($clean_tag !== '') {
                $tag_counts[$clean_tag] = ($tag_counts[$clean_tag] ?? 0) + 1;
            }
        }
    }

    $stmt->close();

    arsort($tag_counts);
    return array_slice(array_keys($tag_counts), 0, $limit);
}


?>