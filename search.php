<?php
/*
Wound Trials Search Platform
PHP search interface for querying the MySQL database containing the
registry-derived database of wound-related clinical trials.

Live interface:
https://healthsmrjournal.com/evidence/search.php

Note:
Database credentials are stored in db.php and are not included
in this public repository.
*/

require_once __DIR__ . "/db.php";

$q = isset($_GET["q"]) ? trim($_GET["q"]) : "";

function h($s){
    return htmlspecialchars((string)$s, ENT_QUOTES, "UTF-8");
}

$results = [];

if ($q !== "") {

    $sql = "SELECT
                nct_number,
                study_title,
                study_url,
                study_status,
                conditions,
                interventions,
                phases,
                enrollment,
                study_type,
                start_date,
                completion_date
            FROM wound_studies_unique
            WHERE nct_number LIKE ?
               OR study_title LIKE ?
               OR conditions LIKE ?
               OR interventions LIKE ?
               OR study_status LIKE ?
               OR study_type LIKE ?
            ORDER BY nct_number ASC
            LIMIT 200";

    $like = "%" . $q . "%";

    if ($stmt = $conn->prepare($sql)) {

        $stmt->bind_param(
            "ssssss",
            $like,
            $like,
            $like,
            $like,
            $like,
            $like
        );

        $stmt->execute();
        $result = $stmt->get_result();

        while ($r = $result->fetch_assoc()) {
            $results[] = $r;
        }

        $stmt->close();

    } else {
        die("SQL prepare failed: " . $conn->error);
    }
}
?>

<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Wound Studies Search</title>

<style>
body{
    font-family:Arial, sans-serif;
    max-width:1100px;
    margin:auto;
    padding:20px;
}
input{
    width:70%;
    padding:12px;
    font-size:16px;
}
button{
    padding:12px 18px;
}
.card{
    border:1px solid #ddd;
    padding:15px;
    margin-top:15px;
    border-radius:8px;
}
.badge{
    border:1px solid #ccc;
    padding:3px 8px;
    margin-right:6px;
    font-size:12px;
    border-radius:12px;
    display:inline-block;
}
.title{
    font-size:18px;
    margin:8px 0;
}
.meta{
    margin-top:6px;
    line-height:1.6;
}
</style>
</head>

<body>

<h2>Wound Studies Search</h2>

<form method="get">
    <input
        type="text"
        name="q"
        value="<?php echo h($q); ?>"
        placeholder="Search studies (example: diabetic foot, venous ulcer, NCT...)"
    >
    <button type="submit">Search</button>
</form>

<br>

<?php if ($q === "") { ?>

<p>Type a keyword to search.</p>

<?php } else { ?>

<p>Showing <?php echo count($results); ?> result(s)</p>

<?php if (count($results) === 0) { ?>
    <p>No results found for <strong><?php echo h($q); ?></strong>.</p>
<?php } ?>

<?php foreach ($results as $r) { ?>

<div class="card">

    <div>
        <?php if (!empty($r["study_type"])) { ?>
            <span class="badge"><?php echo h($r["study_type"]); ?></span>
        <?php } ?>
        <?php if (!empty($r["study_status"])) { ?>
            <span class="badge"><?php echo h($r["study_status"]); ?></span>
        <?php } ?>
        <?php if (!empty($r["phases"])) { ?>
            <span class="badge"><?php echo h($r["phases"]); ?></span>
        <?php } ?>
    </div>

    <div class="title">
        <strong><?php echo h($r["study_title"]); ?></strong>
    </div>

    <div class="meta">
        <div>
            <strong>NCT:</strong>
            <a target="_blank" rel="noopener"
               href="https://clinicaltrials.gov/study/<?php echo h($r["nct_number"]); ?>">
               <?php echo h($r["nct_number"]); ?>
            </a>
        </div>

        <?php if (!empty($r["conditions"])) { ?>
        <div>
            <strong>Conditions:</strong> <?php echo h($r["conditions"]); ?>
        </div>
        <?php } ?>

        <?php if (!empty($r["interventions"])) { ?>
        <div>
            <strong>Interventions:</strong> <?php echo h($r["interventions"]); ?>
        </div>
        <?php } ?>

        <?php if (!empty($r["enrollment"])) { ?>
        <div>
            <strong>Enrollment:</strong> <?php echo h($r["enrollment"]); ?>
        </div>
        <?php } ?>

        <?php if (!empty($r["start_date"])) { ?>
        <div>
            <strong>Start:</strong> <?php echo h($r["start_date"]); ?>
        </div>
        <?php } ?>

        <?php if (!empty($r["completion_date"])) { ?>
        <div>
            <strong>Completion:</strong> <?php echo h($r["completion_date"]); ?>
        </div>
        <?php } ?>

        <?php if (!empty($r["study_url"])) { ?>
        <div>
            <strong>Record:</strong>
            <a target="_blank" rel="noopener" href="<?php echo h($r["study_url"]); ?>">
                Open study page
            </a>
        </div>
        <?php } ?>
    </div>

</div>

<?php } ?>

<?php } ?>

</body>
</html>