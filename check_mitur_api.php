<?php

$errors = false;

function logError($message)
{
    global $errors;
    $errors = true;
    file_put_contents('error_log.txt', $message . PHP_EOL, FILE_APPEND);
}

function fetchJson($url)
{
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode !== 200) {
        logError("Error $httpCode at $url");
        return null;
    }

    $data = json_decode($response, true);
    if (json_last_error() !== JSON_ERROR_NONE || empty($data)) {
        logError("Invalid JSON or empty response at $url");
        return null;
    }

    return $data;
}

function processSectionIds($sectionIds)
{
    foreach ($sectionIds as $sectionId) {
        $sectionData = fetchJson("https://osm2cai.cai.it/api/v2/mitur_abruzzo/section/$sectionId");
        if ($sectionData === null) {
            logError("Failed to fetch section $sectionId");
        }
    }
}

function processHikingRoutes($hikingRoutes)
{
    foreach ($hikingRoutes as $routeId => $timestamp) {
        $routeData = fetchJson("https://osm2cai.cai.it/api/v2/mitur_abruzzo/hiking_route/$routeId");
        if ($routeData === null) {
            logError("Failed to fetch hiking route $routeId");
            continue;
        }

        if (isset($routeData['cai_huts']) && !empty($routeData['cai_huts'])) {
            foreach ($routeData['cai_huts'] as $hutId) {
                $hutData = fetchJson("https://osm2cai.cai.it/api/v2/mitur_abruzzo/hut/$hutId");
                if ($hutData === null) {
                    logError("Failed to fetch hut $hutId");
                }
            }
        }
    }
}

function processMountainGroups($mountainGroups)
{
    foreach ($mountainGroups as $groupId => $timestamp) {
        $groupData = fetchJson("https://osm2cai.cai.it/api/v2/mitur_abruzzo/mountain_group/$groupId");
        if ($groupData === null) {
            logError("Failed to fetch mountain group $groupId");
            continue;
        }

        if (isset($groupData['section_ids']) && !empty($groupData['section_ids'])) {
            processSectionIds($groupData['section_ids']);
        }

        if (isset($groupData['hiking_routes']) && !empty($groupData['hiking_routes'])) {
            processHikingRoutes($groupData['hiking_routes']);
        }

        if (isset($groupData['ec_pois']) && !empty($groupData['ec_pois'])) {
            foreach ($groupData['ec_pois'] as $poiId => $timestamp) {
                $poiData = fetchJson("https://osm2cai.cai.it/api/v2/mitur_abruzzo/poi/$poiId");
                if ($poiData === null) {
                    logError("Failed to fetch POI $poiId");
                }
            }
        }
    }
}

function main()
{
    global $errors;

    $regionsData = fetchJson('https://osm2cai.cai.it/api/v2/mitur_abruzzo/region_list');
    if ($regionsData === null) {
        logError("Failed to fetch region list");
        return;
    }

    foreach ($regionsData as $regionId => $timestamp) {
        $regionData = fetchJson("https://osm2cai.cai.it/api/v2/mitur_abruzzo/region/$regionId");
        if ($regionData === null) {
            logError("Failed to fetch region $regionId");
            continue;
        }

        if (isset($regionData['mountain_groups']) && !empty($regionData['mountain_groups'])) {
            processMountainGroups($regionData['mountain_groups']);
        }
    }

    if (!$errors) {
        file_put_contents('error_log.txt', 'All API responses returned status 200 and were not empty.' . PHP_EOL, FILE_APPEND);
    }
}

main();
