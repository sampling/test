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
    function pregTitles($getFrom)
    {
        preg_match_all(getRegex("regexTitle"), $getFrom, $titlesArray);
        $titlesArray[0] = str_replace("class=\"b-forecast__table-description-title\"", "class=\"scraped__title\"", $titlesArray[0]);

        function makeSpan($makeFrom)
        {
            for ($i = 0; $i < sizeof($makeFrom[0]); $i++) {
                preg_match(getRegex("regexSubTitle"), $makeFrom[0][$i], $subTitle);

                $makeFrom[0][$i] = preg_replace(getRegex("regexSubTitle"), "<span>" . $subTitle[0] . "</span>", $makeFrom[0][$i]);
            }
            return $makeFrom;
            //
        }
        $titlesArray = makeSpan($titlesArray);
        function correctHTML($toBeCorrected)
        {
            for ($i = 0; $i < sizeof($toBeCorrected[0]); $i++) {
                if (preg_match(getRegex("regexSpanInHeading"), $toBeCorrected[0][$i])) {
                    preg_match(getRegex("regexSpanElement"), $toBeCorrected[0][$i], $subTitle);
                    $toBeCorrected[0][$i] = preg_replace(getRegex("regexSpanElement"), " For The Week", $toBeCorrected[0][$i]);
                    preg_match(getRegex("regexMyTitles"), $toBeCorrected[0][$i], $title);
                    $toBeCorrected[0][$i] = preg_replace(getRegex("regexMyTitles"), $title[0] . $subTitle[0], $toBeCorrected[0][$i]);
                }
            }
            return $toBeCorrected[0];
        }
        $titlesArray = correctHTML($titlesArray);

        return $titlesArray;
    }
    $titlesArray = pregTitles($getFrom);

    function takeSpanOnly($titlesArray)
    {
        for ($i = 0; $i <= sizeof($titlesArray); $i++) {
            preg_match(getRegex("regexSpanElement"), $titlesArray[$i], $subTitlesArray[$i]);
        }
        return $subTitlesArray;
    }
    $titlesArray = takeSpanOnly($titlesArray);

    return $titlesArray;
}


function getContent($getFrom)
{
    function pregContent($getFrom)
    {
        preg_match_all(getRegex("regexContent"), $getFrom, $contentArray);

        $contentArray[0] = str_replace("class=\"b-forecast__table-description-content\"",
            "class=\"scraped__content\"", $contentArray[0]);

        $contentArray[0] = preg_replace(getRegex("regexSpan"), "", $contentArray[0]);

        return $contentArray[0];
    }
    $contentArray = pregContent($getFrom);

    return $contentArray;
}

function makeHTML($titles, $content)
{
    // create HTML structure to work with jQueryUI's tabs widget
    // api docs: https://api.jqueryui.com/tabs/
    function makeTabTitles($titles)
    {
        $string = "<ul>";

        for ($i = 0; $i < sizeof($titles) - 1; $i++) {
            $scrapedTitles[$i] = "<li><a href=\"#tab-{$i}\">" . $titles[$i][0] . "</a></li>";
            $string .= $scrapedTitles[$i];
        }
        $string .= "</ul>";
        return $string;

    }
    $tabTitles = makeTabTitles($titles);

    function makeTabContent($content)
    {
        $string = "";

        for ($i = 0; $i < sizeof($content); $i++) {
            $string .= "<div id=\"tab-{$i}\">" . $content[$i] . "</div>";
        }

        return $string;
    }
    $tabContent = makeTabContent($content);

    function makeContainer($tabTitles, $tabContent)
    {
        $string = "<div id=\"tabs\">";

        $string .= $tabTitles;

        $string .= $tabContent;

        for ($i = 0; $i < sizeof($tabTitles); $i++) {
            if ($i == sizeof($tabTitles) - 1) {
                $string .= "</div>";
            }
        }
        return $string;

    }
    $string = makeContainer($tabTitles, $tabContent);
    return $string;
}
