<?php
// Define the log file path
$logFile = 'AllDataFromBitrix.txt';
//After Api hit
$logFile1 = 'Customer_API_status.txt';
$logFile2 = 'Deal_API_Status';
$logFileSend = 'CustomerDataToDocmate.txt';
$deallog= 'DealDataToDocmate.txt';

// Function to log data to the file
function logData($data, $file) {
    $logEntry = "==============================\n";
    $logEntry .= "Timestamp: " . date('Y-m-d H:i:s') . "\n";
    foreach ($data as $key => $value) {
        $logEntry .= ucfirst($key) . ": " . json_encode($value) . "\n";
    }
    $logEntry .= "==============================\n\n";
    file_put_contents($file, $logEntry, FILE_APPEND);
}

// Capture incoming data from the webhook
$incomingData = array(
    'Salutation' => $_GET['Salutation'] ?? 'Mr',
    'First_Name' => $_GET['First_Name'] ?? '',
    'Second_Name' => $_GET['Second_Name'] ?? '',
    'Last_Name' => $_GET['Last_Name'] ?? '',
    'Gender' => $_GET['Gender'] ?? 'M',
    'Photo' => $_GET['Photo'] ?? '',
    'DOB' => $_GET['DOB'] ?? '05/Jul/2000', 
    'Mobile' => $_GET['Mobile'] ?? '971',
    'Email' => $_GET['Email'] ?? 'abc@gmail.com', 
    'Address' => $_GET['Address'] ?? '',
    'FileNo' => $_GET['FileNo'] ?? '',
    'PatBitrixId' => $_GET['PatBitrixId'] ?? '',
    'IqamaNo' => $_GET['IqamaNo'] ?? '', 
    'NationalityId' => $_GET['NationalityId'] ?? '361', 
    'EnteredBy' => $_GET['EnteredBy'] ?? '001', 
    'countryCode' => $_GET['countryCode'] ?? '',
    'MSC_code' => $_GET['MSC_code'] ?? 'S',
    // Data for Create Deals API
    'patientAppointmentId'=> $_GET['PatientAppointmentID'] ?? 0, 
    'visitDate' => $_GET['VisitDate'] ?? '',
    'doctorName' => $_GET['DoctorId'] ?? '',
    'tokenNo' =>$_GET['TokenNo'] ?? '0',
    'appointmentNo' =>$_GET['AppointmentNo'] ?? '',
    'eventStart' => $_GET['eventstart'] ?? '',
    'duration'=> $_GET['duration'] ?? 30,
    'patientType' =>$_GET['PatientType'] ?? 'Walkin', 
    'walkInTime' => $_GET['WalkInTime'] ?? '',
    'reason' => $_GET['Reason'] ?? 'Unknown',
    'visitStatus' => $_GET['VisitStatus'] ?? '1', 
    'patBitrixDealId' => $_GET['PatBitrixDealId'] ?? '',
    'services' => $_GET['services'] ?? '',
    'serviceID' => $_GET['ser2'] ?? '',
    'stage' => $_GET['stage'] ?? '',
    'personID'=>$_GET['responsible'],
    'doctorID'=>$_GET['doc_id2'],
    'expense'=>$_GET['income']
);

// Log the incoming data
logData($incomingData, $logFile);

// Process the gender field
$processedGender = '';
if (strtolower($incomingData['Gender']) == 'male') {
    $processedGender = 'M';
} elseif (strtolower($incomingData['Gender']) == 'female') {
    $processedGender = 'F';
}

//process EnteredBY Here
$check = $incomingData['personID'];
preg_match('/\d+/', $check, $matches);
$calpersonid = intval($matches[0]);

// Data for Create Customer API
$customerData = array(
    'Salutation' => $incomingData['Salutation'],
    'First_Name' => $incomingData['First_Name'],
    'Second_Name' => $incomingData['Second_Name'],
    'Last_Name' => $incomingData['Last_Name'],
    'Gender' => $processedGender,
    'Photo' => $incomingData['Photo'],
    'DOB' => $incomingData['DOB'],
    'Mobile' => $incomingData['Mobile'],
    'Email' => $incomingData['Email'],
    'Address' => $incomingData['Address'],
    'FileNo' => $incomingData['FileNo'],
    'PatBitrixId' => intval($incomingData['PatBitrixId']),
    'IqamaNo' => $incomingData['IqamaNo'],
    'NationalityId' => $incomingData['NationalityId'],
    'EnteredBy' =>  165162,
    'countryCode' => $incomingData['countryCode'],
    'MSC_code' => $incomingData['MSC_code']
);


//Calculating End Time
// $startTime = $incomingData['visitDate'];
// $endTime = clone $startTime;
// $endTime->modify("+{$duration} minutes");

// Ensure visitDate is a string
$visitDate = (string)$incomingData['visitDate'];

if (!empty($visitDate)) {
    // Convert visitDate string to DateTime object
    $startTime = DateTime::createFromFormat('d/m/Y h:i:s a', $visitDate);

    // Check if the conversion was successful
    if ($startTime !== false) {
        // Extract duration
        $duration = (int)$incomingData['duration'];

        // Calculate End Time
        $endTime = clone $startTime;
        $endTime->modify("+{$duration} minutes");
    }
}

//Switch Case

switch($incomingData['stage']){
    case "Booking Done":
    $visitstatus=5;
    break;
    case "No Show":
    $visitstatus=0;
    break;
    case "Arrived":
    $visitstatus=5;
    break;
    case "Invoiced":
    $visitstatus=1;
    break;
    case "Cancelled":
    $visitstatus=9;
    break;
    }

$deal = array(
    "PatientAppointmentID" => "0",
    "VisitDate" => $startTime->format('Y-m-d\TH:i:s'),
    "DoctorId" => $incomingData['doctorID'],
    "AppointmentNo" => $incomingData['appointmentNo'],
    "TokenNo" => "0",
    "VisitStatus"=>intval($visitstatus),
    "eventstart" => $startTime->format('Y-m-d\TH:i:s'),
    "eventend" => $endTime->format('Y-m-d\TH:i:s'),
    "PatientType" => "Walkin",
    "WalkInTime" =>$startTime->format('Y-m-d\TH:i:s'),
    "Reason" => $incomingData['reason'],
    "PatBitrixId" =>intval($incomingData['PatBitrixId']),
    "PatBitrixDealId" =>intval($incomingData['patBitrixDealId']),
    'EnteredBy' =>  $calpersonid 
);
$deal['services']=array(
    array("serviceID"=>$incomingData['serviceID'],"serviceName"=>$incomingData['services'],"cptCode"=>"85060","qty"=>1,"expense"=>$incomingData['expense'])
);

// Log the data (Customer) being sent to the API
logData($customerData, $logFileSend);
// Log the data (Deal) being sent to the API
logData($deal, $deallog);

// Function to send data using cURL
function sendData($url, $data) {
    $jsonData = json_encode($data);
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
    $response = curl_exec($ch);
    if(curl_errno($ch)) {
        $response = 'Curl error: ' . curl_error($ch);
    }
    curl_close($ch);
    return $response;
}

// Send data to Create Customer API
$customerResponse = sendData('https://apibitrix.docmate.app/Data/AddCustomer', $customerData);

//Creating Deal
$dealResponse = sendData('https://apibitrix.docmate.app/Data/AddDeals', $deal);

// Log the API response
logData(array('CustomerResponse' => $customerResponse), $logFile1);
logData(array('dealResponse' => $dealResponse), $logFile2);


// Output the API response (for debugging purposes)
echo "Customer Response: " . $customerResponse;
echo "Deal Response: " . $dealResponse;

//checking push
?>

