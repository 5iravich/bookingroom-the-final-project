<?php 
session_start();
define ('PDF_FONT_NAME_MAIN','thsarabun');

// Include the main TCPDF library (search for installation path).
require_once('tcpdf.php');
require_once './config/db.php';

class MYPDF extends TCPDF {

    //Page header
    public function Header() {
        // Logo
        // Set font
        $this->SetFont('thsarabun', 'B', 12);
        // Title
        $this->Cell(0, 15, 'KU-KPS-Dormitory', 0, false, 'L', 0, '', 0, false, 'M', 'M');
    }

    // Page footer
    public function Footer() {
        // Position at 15 mm from bottom
        $this->SetY(-15);
        // Set font
        $this->SetFont('thsarabun', 'I', 8);
        // Page number
        $this->Cell(0, 10, 'หน้า '.$this->getAliasNumPage().'/'.$this->getAliasNbPages(), 0, false, 'R', 0, '', 0, false, 'T', 'M');
    }
}
// create new PDF document
$pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

// set document information
$pdf->setCreator(PDF_CREATOR);
$pdf->setAuthor('Siravich Supaveerasathien');
$pdf->setTitle('KUDM-PROOFPAYMENT');
$pdf->setSubject('Summeries');
$pdf->setKeywords('PDF, project, test');

// remove default header/footer
$pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE, PDF_HEADER_STRING);
// $pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH,'ADMIN-REPORT', 'KU-KPS-Dormitory');
$pdf->SetFooterData();
$pdf->setPrintHeader(true);
$pdf->setPrintFooter(true);

// set default monospaced font
$pdf->setDefaultMonospacedFont(PDF_FONT_MONOSPACED);

// set margins
$pdf->setMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

// set auto page breaks
$pdf->setAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

// set image scale factor
$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

// set some language-dependent strings (optional)
if (@file_exists(dirname(__FILE__).'/lang/eng.php')) {
	require_once(dirname(__FILE__).'/lang/eng.php');
	$pdf->setLanguageArray($l);
}

// ---------------------------------------------------------
$stdid = $_SESSION['user_login'];
    $sql_user = "SELECT gender FROM users WHERE stdid = :stdid";
    $stmt_user = $conn->prepare($sql_user);
    $stmt_user->bindParam(':stdid', $stdid, PDO::PARAM_STR);
    $stmt_user->execute();
    $user = $stmt_user->fetch(PDO::FETCH_ASSOC);

$sql_booking_info = "SELECT *, slip_image FROM bookings
                     JOIN rooms ON bookings.room_id = rooms.room_id
                     WHERE bookings.stdid = :stdid";
    $stmt_booking_info = $conn->prepare($sql_booking_info);
    $stmt_booking_info->bindParam(':stdid', $stdid, PDO::PARAM_STR);
    $stmt_booking_info->execute();
    $booking_info = $stmt_booking_info->fetch(PDO::FETCH_ASSOC);

	$user_sql = "SELECT * FROM users WHERE stdid = :stdid";
    $user_stmt = $conn->prepare($user_sql);
    $user_stmt->bindParam(':stdid', $stdid, PDO::PARAM_STR);
    $user_stmt->execute();
    $user_info = $user_stmt->fetch(PDO::FETCH_ASSOC);

$pdf->setFontSubsetting(false);

$pdf->AddPage('A4');

$pdf->setFont('thsarabun', 'B', 20);
$pdf->Write(0, "หลักฐานการชำระเงิน", '', 0, 'C', true, 0, false, false, 0);

if ($booking_info) {
    $stdid = htmlspecialchars($booking_info['stdid']);
	$firstname = htmlspecialchars($user_info['firstname']);
	$lastname = htmlspecialchars($user_info['lastname']);
    $dorm_id = htmlspecialchars($booking_info['dorm_id']);
    $room_number = htmlspecialchars($booking_info['room_number']);
    $booking_status = $booking_info['statuspay'];

    $content = "$stdid $firstname $lastname คุณมีการจองหอพักแล้ว\n";
    $content .= "ตึกที่: $dorm_id ห้อง: $room_number\n";

    if ($booking_status === 'pending') {
        $payment_status = 'รอดำเนินการ';
        $status_color = 'yellow';
    } elseif ($booking_status === 'confirmed') {
        $payment_status = 'ชำระเงินแล้ว';
        $status_color = 'green';
    } else {
        $payment_status = 'ไม่ได้ชำระเงิน';
        $status_color = 'red';
    }

    $content .= "สถานะการชำระเงิน: $payment_status\n";

	$pdf->SetDrawColor(0, 128, 0);
	$pdf->SetLineWidth(0.3);
    $pdf->Rect(10, 20, 190, 50);
    $pdf->SetFont('thsarabun', '', 16);
    $pdf->MultiCell(0, 10, $content, 0, 'L');

} else {
    $content = "คุณยังไม่มีการจองหอพัก";
    $pdf->SetFont('thsarabun', '', 16);
    $pdf->MultiCell(0, 10, $content, 0, 'L');
}

$pdf->Output('KUDM-PROOFPAYMENT.pdf', 'I');

?>