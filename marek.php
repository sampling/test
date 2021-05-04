<?php 

function getWeather($location)
{
    $getFrom = "https://www.weather-forecast.com/locations/" . $location . "/forecasts/latest";
    $scrapedContent = file_get_contents($getFrom);
    
    // extract wanted bits from scraped HTML
    preg_match(getRegex("regexScraped"), $scrapedContent, $elementArray);

    // save extracted array in a variable
    $result = $elementArray[0];
    
    // any HTML content
    if ($result) {
        // prepare data
        $titlesArray = getTitles($result);
        $contentArray = getContent($result);
        $string = makeHTML($titlesArray, $contentArray);
        return $string;
    } else {
        // no HTML content, despite sending a request
        if ($location) {
            return "<div class=\"alert-danger\"><p class=\"error__message\">Please, make sure the phrase you typed in is a name of an existing city!</p></div>";
        }
    }
}

function getTitles($getFrom)
{
    // get title
    preg_match_all(getRegex("regexTitle"), $getFrom, $titlesArray);
    $titlesArray[0] = str_replace("class=\"b-forecast__table-description-title\"", "class=\"scraped__title\"", $titlesArray[0]);
    
    // get subtitle
    for ($i = 0; $i < sizeof($titlesArray[0]); $i++) {
        preg_match(getRegex("regexSubTitle"), $$titlesArray[0][$i], $subTitle);
        $titlesArray[0][$i] = preg_replace(getRegex("regexSubTitle"), "<span>" . $subTitle[0] . "</span>", $titlesArray[0][$i]);
        // correct title & subtitle HTML if needed
        if (preg_match(getRegex("regexSpanInHeading"), $titlesArray[0][$i])) {
            preg_match(getRegex("regexSpanElement"), $titlesArray[0][$i], $subTitle);
            $titlesArray[0][$i] = preg_replace(getRegex("regexSpanElement"), " For The Week", $titlesArray[0][$i]);
            preg_match(getRegex("regexMyTitles"), $toBeCorrected[0][$i], $title);
            $titlesArray[0][$i] = preg_replace(getRegex("regexMyTitles"), $title[0] . $subTitle[0], $titlesArray[0][$i]);
        }
    } 
    for ($i = 0; $i <= sizeof($titlesArray); $i++) {
        preg_match(getRegex("regexSpanElement"), $titlesArray[$i], $subTitlesArray[$i]);
    }
    return $subTitlesArray;
}

function getContent($getFrom)
{
    preg_match_all(getRegex("regexContent"), $getFrom, $contentArray);
    $contentArray[0] = str_replace("class=\"b-forecast__table-description-content\"",
        "class=\"scraped__content\"", $contentArray[0]);
    $contentArray[0] = preg_replace(getRegex("regexSpan"), "", $contentArray[0]);
    return $contentArray[0];
}

function makeHTML($titles, $content)
{
    // create HTML structure to work with jQueryUI's tabs widget
    // api docs: https://api.jqueryui.com/tabs/
    
    $outputHTML = "<div id=\"tabs\">";
    $outputHTML .= "<ul>";
    
    // append tabHTML
    for ($i = 0; $i < sizeof($titles) - 1; $i++) {
        $outputHTML .= "<li><a href=\"#tab-{$i}\">" . $titles[$i][0] . "</a></li>";
    }
    $outputHTML .= "</ul>";
    
    // append contentHTML
    for ($i = 0; $i < sizeof($content); $i++) {
        $outputHTML .= "<div id=\"tab-{$i}\">" . $content[$i] . "</div>";
    }
    $outputHTML .= "</div>";
    return $outputHTML;
}
?>
