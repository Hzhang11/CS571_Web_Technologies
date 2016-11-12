<?php
    define('APIKEY', '8254cc34a8b943bfb41d348cca173ccb');
    $forClear = isset($_POST['resetButton']);
    $forSubmit = isset($_POST['submitButton']);
    if ($forClear) {
        $_POST = array();
    }

    if($forSubmit) {
        $dbName = htmlspecialchars($_POST['database']);
        $chamber = htmlspecialchars($_POST['chamber']);
        $keyword = htmlspecialchars($_POST['key']);
    }
    $query_var = "query";
    function state_abbr($input) {
        global $query_var;
        $states = array (
            'Alabama'=>'AL',
            'Alaska'=>'AK',
            'Arizona'=>'AZ',
            'Arkansas'=>'AR',
            'California'=>'CA',
            'Colorado'=>'CO',
            'Connecticut'=>'CT',
            'District Of Columbia'=>'DC',
            'Delaware'=>'DE',
            'Florida'=>'FL',
            'Georgia'=>'GA',
            'Hawaii'=>'HI',
            'Idaho'=>'ID',
            'Illinois'=>'IL',
            'Indiana'=>'IN',
            'Iowa'=>'IA',
            'Kansas'=>'KS',
            'Kentucky'=>'KY',
            'Louisiana'=>'LA',
            'Maine'=>'ME',
            'Maryland'=>'MD',
            'Massachusetts'=>'MA',
            'Michigan'=>'MI',
            'Minnesota'=>'MN',
            'Mississippi'=>'MS',
            'Missouri'=>'MO',
            'Montana'=>'MT',
            'Nebraska'=>'NE',
            'Nevada'=>'NV',
            'New Hampshire'=>'NH',
            'New Jersey'=>'NJ',
            'New Mexico'=>'NM',
            'New York'=>'NY',
            'North Carolina'=>'NC',
            'North Dakota'=>'ND',
            'Ohio'=>'OH',
            'Oklahoma'=>'OK',
            'Oregon'=>'OR',
            'Pennsylvania'=>'PA',
            'Rhode Island'=>'RI',
            'South Carolina'=>'SC',
            'South Dakota'=>'SD',
            'Tennessee'=>'TN',
            'Texas'=>'TX',
            'Utah'=>'UT',
            'Vermont'=>'VT',
            'Virginia'=>'VA',
            'Washington'=>'WA',
            'West Virginia'=>'WV',
            'Wisconsin'=>'WI',
            'Wyoming'=>'WY'
        );

        foreach( $states as $name=>$abbr ) {
            if ( $name == ucwords(strtolower($input))) {
                $query_var = "state";
                return $abbr;
            }
            
        }
        return $input;
    }

    function displayLegislator($json_input) {
        if ($json_input->count == 0) {
            echo "<B>The API returned zero result for the quest</B><br>";
            return;
        }
        $table = "<table id='result_tbl'><tr><th width='20%'>Name</th><th width='20%'>State</th><th width='20%'>Chamber</th><th width='40%'>Details</th>";
        
        for ($i = 0; $i < $json_input->page->count; $i++) {
            $name = $json_input->results[$i]->first_name . " " . $json_input->results[$i]->last_name;
            $state_name = $json_input->results[$i]->state_name;
            $chamber_name = $json_input->results[$i]->chamber;
            $data = json_encode($json_input->results[$i]);
            $link = "<a href='javascript:void(0)' onclick='displayLegislatorDetail($data)'>View Details</a>";
            $table .= "<tr><td><div style='text-align: left; margin-left:25%;'>$name</div></td><td>$state_name</td><td>$chamber_name</td><td>$link</td>";
        }
        
        $table .= "</table>";
        echo $table;
    }


    function displayCommittee($json_input) {
        if ($json_input->count == 0) {
            echo "<B>The API returned zero result for the quest</B><br>";
            return;
        }
        $table = "<table id='result_tbl'><tr><th width='20%'>Committee ID</th><th width='60%'>Committee Name</th><th width='20%'>Chamber</th>";
        
        for ($i = 0; $i < $json_input->page->count; $i++) {
            $id = $json_input->results[$i]->committee_id;
            $committee_name = $json_input->results[$i]->name;
            $chamber_name = $json_input->results[$i]->chamber;
            $table .= "<tr><td>$id</td><td>$committee_name</td><td>$chamber_name</td>";
        }
        
        $table .= "</table>";
        echo $table;
    }

    function displayBill($json_input) {
        if ($json_input->count == 0) {
            echo "<B>The API returned zero result for the quest</B><br>";
            return;
        }
        $table = "<table id='result_tbl'><tr><th width='20%'>Bill ID</th><th width='40%'>Short Title</th><th width='20%'>Chamber</th><th width='20%'>Details</th>";
        
        for ($i = 0; $i < $json_input->page->count; $i++) {
            $bill_id = $json_input->results[$i]->bill_id;
            $title = $json_input->results[$i]->short_title;
            $chamber_name = $json_input->results[$i]->chamber;
            $data = json_encode($json_input->results[$i]);
            $link = "<a href='javascript:void(0)' onclick='displayBillDetail($data)'>View Details</a>";
            $table .= "<tr><td><div style='text-align: left; margin-left:25%;'>$bill_id</div></td><td>$title</td><td>$chamber_name</td><td>$link</td>";
        }
        
        $table .= "</table>";
        echo $table;
    }

    function displayAmendment($json_input) {
        if ($json_input->count == 0) {
            echo "<B>The API returned zero result for the quest</B><br>";
            return;
        }
        $table = "<table id='result_tbl'><tr><th width='30%'>Amendment ID</th><th width='20%'>Amendment Type</th><th width='20%'>Chamber</th><th width='30%'>Introduced on</th>";
        
        for ($i = 0; $i < $json_input->page->count; $i++) {
            $id = $json_input->results[$i]->amendment_id;
            $type = $json_input->results[$i]->amendment_type;
            $chamber_name = $json_input->results[$i]->chamber;
            $intro_date = $json_input->results[$i]->introduced_on;
            $table .= "<tr><td>$id</td><td>$type</td><td>$chamber_name</td><td>$intro_date</td>";
        }
        
        $table .= "</table>";
        echo $table;
    }

?>
    <!doctype html>
    <html lang="en">

    <head>
        <meta charset="utf-8">
        <title>Congress Search</title>
        <script type="text/javascript">
            function updateKeyword() {
                var input = document.getElementById("database");
                var keywords = {
                    '-1': 'Keyword*'
                    , 'legislators': 'State/Representative*'
                    , 'committees': 'Committee ID*'
                    , 'bills': 'Bill ID*'
                    , 'amendments': 'Amendment ID*'
                };
                var value = input.options[input.selectedIndex].value;
                document.getElementById("keyword").innerHTML = keywords[value];
            }

            function validateForm() {
                var dbSelection = document.getElementById("database");
                var dbName = dbSelection.options[dbSelection.selectedIndex].value;
                if (dbName == null || dbName == "" || dbName == -1) {
                    alert("Please select a congress database");
                    return false;
                }
                var radioButton = document.querySelector('input[name="chamber"]:checked');
                if (radioButton == null) {
                    alert("Please select a chamber");
                    return false;
                }
                var keyword = document.getElementById("key").value.trim();
                if (keyword == null || keyword == "") {
                    alert("Please enter a keyword to search for");
                    return false;
                }
                return true;
            }
            
            function displayLegislatorDetail(json_input) {
                var image_link = "<img src='https://theunitedstates.io/images/congress/225x275/" + json_input.bioguide_id + ".jpg'>";
                var title = json_input.title;
                var full_name = json_input.first_name + " " + json_input.last_name;
                var term_end = json_input.term_end;
                var web_link = "<a href='" + json_input.website + "' target='_blank'>" + json_input.website + "</a>";
                var office = json_input.office;
                var facebook = json_input.facebook_id == null ? "N/A" : "<a href='https://www.facebook.com/" + json_input.facebook_id +"'>" + full_name + "</a>";
                var twitter = json_input.twitter_id == null ? "N/A" : "<a href='https://twitter.com/" + json_input.twitter_id +"'>"+ full_name + "</a>";
                
                var table = "<div class='detail_wrapper'><table id='detail_tbl' style='margin:20px auto 20px auto;text-align:left;'><tr><td colspan='2' style='text-align:center;'>" + image_link + "</td></tr>";
                table += "<tr><td width='200px'>Full Name</td><td width='300px'>" + title + " " + full_name +"</td></tr>";
                table += "<tr><td>Term Ends on</td><td>" + term_end +"</td></tr>";
                table += "<tr><td>Website</td><td>" + web_link +"</td></tr>";
                table += "<tr><td>Office</td><td>" + office +"</td></tr>";
                table += "<tr><td>Facebook</td><td>" + facebook +"</td></tr>";
                table += "<tr><td>Twitter</td><td>" + twitter +"</td></tr></table></div>";
                document.getElementById("table_wrapper").innerHTML = table;
            
            }
            
            function displayBillDetail(json_input) {
                var bill_id = json_input.bill_id;
                var bill_title = json_input.short_title;
                var sponsor = json_input.sponsor.title + " " + json_input.sponsor.first_name + " " + json_input.sponsor.last_name;
                var intro_date = json_input.introduced_on;
                var last_action = json_input.last_version.version_name + ", " + json_input.last_action_at;
                var bill_url = "<a href='" + json_input.last_version.urls.pdf + "'>" + bill_title + "</a>";
                
                var table = "<div class='detail_wrapper'><table id='detail_tbl' style='margin:20px auto 20px auto;text-align:left;'>";
                table += "<tr><td width='300px'>Bill ID</td><td width='300px'>" + bill_id +"</td></tr>";
                table += "<tr><td>Bill Title</td><td>" + bill_title +"</td></tr>";
                table += "<tr><td>Sponsor</td><td>" + sponsor +"</td></tr>";
                table += "<tr><td>Introduced On</td><td>" + intro_date +"</td></tr>";
                table += "<tr><td>Last action with date</td><td>" + last_action +"</td></tr>";
                table += "<tr><td>Bill URL</td><td>" + bill_url +"</td></tr></table></div>";
                document.getElementById("table_wrapper").innerHTML = table;
            }
            window.onload = updateKeyword;
        </script>
        <style>
            .wrapper {
                text-align: center;
                margin: 50px auto;
                width: 300px;
            }
            
            #table_wrapper{
                width: 1000px;
                margin: auto;
                text-align: center;
            }
            
            B {
                font-size: 25px;
            }
            
            #queryTable {
                border-collapse: collapse;
                border: 1px black solid;
            }
            

            #result_tbl, #result_tbl th, #result_tbl td {
                border-collapse: collapse;
                text-align: center;
                border: 1px black solid;
            }
            
            #result_tbl {
                width: 1000px;
                
            }
            
            .detail_wrapper {
                border: 1px black solid;
            }

        </style>
    </head>

    <body>

        <div class="wrapper">
            <form name="queryForm" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="POST">
                <table id="queryTable" border="0">
                    <tr>
                        <td align="center" style="width:150px;">Congress Database</td>
                        <td align="center" style="width:150px;">
                            <select name="database" id="database" onchange="updateKeyword();">
                                <option value="-1" <?php if($forClear) { echo 'selected';} ?>>Select your option</option>
                                <option value="legislators" <?php if($forSubmit && $dbName=='legislators' ) { echo 'selected';} ?>>Legislators</option>
                                <option value="committees" <?php if($forSubmit && $dbName=='committees' ) { echo 'selected';} ?>>Committees</option>
                                <option value="bills" <?php if($forSubmit && $dbName=='bills' ) { echo 'selected';} ?>>Bills</option>
                                <option value="amendments" <?php if($forSubmit && $dbName=='amendments' ) { echo 'selected';} ?>>Amendments</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td align="center">Chamber</td>
                        <td align="center">
                            <input type="radio" name="chamber" value="senate" <?php if($forSubmit && $chamber=='senate' ) { echo 'checked';} ?>>Senate
                            <input type="radio" name="chamber" value="house" <?php if($forSubmit && $chamber=='house' ) { echo 'checked';} ?>>House </td>
                    </tr>
                    <tr>
                        <td id="keyword" align="center">Keyword*</td>
                        <td align="center">
                            <input id="key" type="text" name="key" value="<?php echo isset($_POST['key'])?$_POST['key']:'' ?>"> </td>
                    </tr>
                    <tr>
                        <td></td>
                        <td align="center">
                            <input type="submit" name="submitButton" value="Search" onclick="return validateForm();">
                            <input type="submit" name="resetButton" value="Clear"> </td>
                    </tr>
                    <tr>
                        <td align="center" colspan="2"><a href="http://sunlightfoundation.com/" target="_blank">Powered By Sunlight Foundation</a></td>
                    </tr>
                </table>
            </form>
        </div>
        <div id="table_wrapper">
            <?php
                if ($forSubmit) {
                    $url = 'https://congress.api.sunlightfoundation.com/';
                    switch ($dbName) {
                        case "legislators":
                            $keyword = state_abbr($_POST['key']);
                            $key_array = explode(" ", $keyword);
                            if ($query_var == "query" && sizeof($key_array) > 1) {
                                $first_name = $key_array[0];
                                $last_name = $key_array[1];
                                $query = "chamber=$chamber&first_name=$first_name&last_name=$last_name";
                            } else {
                                $query = "chamber=$chamber&$query_var=$keyword";
                            }
                            $url = $url . $dbName . '?' . $query;
                            $response = file_get_contents($url . "&apikey=".APIKEY);
                            $jsonobj = json_decode($response);
                            displayLegislator($jsonobj);
                            break;

                        case "committees":
                            $query = "committee_id=$keyword&chamber=$chamber";
                            $url = $url . $dbName . '?' . $query;
                            $response = file_get_contents($url . "&apikey=" . APIKEY);
                            $jsonobj = json_decode($response);
                            displayCommittee($jsonobj);
                            break;
                            
                        case "bills":
                            $query = "bill_id=$keyword&chamber=$chamber";
                            $url = $url . $dbName . '?' . $query;
                            $response = file_get_contents($url . "&apikey=" . APIKEY);
                            $jsonobj = json_decode($response);
                            displayBill($jsonobj);
                            break;
                            
                        case "amendments":
                            $query = "amendment_id=$keyword&chamber=$chamber";
                            $url = $url . $dbName . '?' . $query;
                            $response = file_get_contents($url . "&apikey=" . APIKEY);
                            $jsonobj = json_decode($response);
                            displayAmendment($jsonobj);
                            break;
                            
                    }
                    echo $url . "&apikey=" . APIKEY . "<br>";
                }
            ?>
        </div>
    </body>

    </html>