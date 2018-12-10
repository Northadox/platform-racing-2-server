<?php


function guild_invitation_delete($pdo, $guild_id, $user_id)
{
    $stmt = $pdo->prepare('
        DELETE FROM guild_invitations
         WHERE guild_id = :guild_id
           AND user_id = :user_id
    ');
    $stmt->bindValue(':guild_id', $guild_id, PDO::PARAM_INT);
    $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
    $result = $stmt->execute();

    if ($result === false) {
        throw new Exception('Could not delete guild invitation.');
    }

    return $result;
}


function guild_invitation_insert($pdo, $guild_id, $user_id)
{
    $stmt = $pdo->prepare('
        INSERT INTO guild_invitations
           SET guild_id = :guild_id,
               user_id = :user_id,
               date = NOW()
    ');
    $stmt->bindValue(':guild_id', $guild_id, PDO::PARAM_INT);
    $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
    $result = $stmt->execute();

    if ($result === false) {
        throw new Exception('Could not create guild invitation.');
    }

    return $result;
}


function guild_invitation_select($pdo, $guild_id, $user_id)
{
    $stmt = $pdo->prepare('
        SELECT *
          FROM guild_invitations
         WHERE guild_id = :guild_id
           AND user_id = :user_id
         LIMIT 1
    ');
    $stmt->bindValue(':guild_id', $guild_id, PDO::PARAM_INT);
    $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
    $result = $stmt->execute();

    if ($result === false) {
        throw new Exception('Could not select guild invitation.');
    }

    $invite = $stmt->fetch(PDO::FETCH_OBJ);

    if (empty($invite)) {
        throw new Exception("Could not find an invite for you to join guild #$guild_id.");
    }

    return $invite;
}
