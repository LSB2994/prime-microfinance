<?php
/**
 * ==========================================================================
 * Loan Schedule Data Reader (Database Query - NOT an API)
 * ==========================================================================
 * 
 * Reads loan repayment schedule data from database for a given ACNO
 * This is just a database query, not an API endpoint
 * ==========================================================================
 */

// Start output buffering to prevent any HTML output
ob_start();

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/interceptor.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/helpers.php';

// Clear any output and set JSON response header
ob_clean();
header('Content-Type: application/json');

// Check authentication
if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'message' => 'Unauthorized'
    ]);
    exit;
}

// Get ACNO from query parameter
$acno = $_GET['acno'] ?? '';

if (empty($acno)) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'ACNO parameter is required'
    ]);
    exit;
}

$scheduleRows = [];
$scheduleError = null;
$summary = [
    'totalDays' => 0,
    'totalPrincipal' => 0,
    'totalInterest' => 0,
    'totalAmount' => 0,
    'initialBalance' => 0
];

try {
    // Read schedule data from database using raw SQL query
    $sql = "SELECT  
        BI.LH_F_FORMAT_ACCCODE(c.ACNO, 'CR') AS ACNO,
        v.DUENO,
        case when c.INTMODE IN ('L','C','B') then
            (select count(*) from o9cbs.D_CRSCHD ch where ch.defacno=c.defacno AND RPTYPE='P')
        WHEN C.INTMODE IN ('F') THEN
            (select count(*) from o9cbs.D_CRSCHDEST ch where ch.rptype='E' and ch.defacno=c.defacno)
        end as PERIOD,
        v.DUEDATE, 
        BI.lh_f_dayname_kh(to_date(v.DUEDATE, 'dd-mm-yy')) as DAYNAME,
        BI.LH_F_GET_BAL_AMT(v.DEFACNO,v.DUEDATE ) as BL,
        v.INTEREST as INTEREST,
        v.PRINCIPAL as PRINCIPAL,
        v.feeamt,
        v.AdminFeeRate,
        case when c.CCRCD='USD' then (v.INTEREST + v.PRINCIPAL+v.feeamt)
        when c.CCRCD='KHR' then (v.INTEREST + v.PRINCIPAL+v.feeamt) end as TOTALAMOUNT,
        (select cu.ADDRESS from o9cbs.d_customer cu where cu.customerid=c.customerid) as Adress_Migrate,
        ceil(v.INTEREST + v.PRINCIPAL) as TOTALPAID,
        v.DAYS, 
        c.ACNAME, 
        BI.LH_F_FORMAT_ACCCODE(
            case c.ctmtype when 'C' then
                (select cu.customercd from o9cbs.d_customer cu where cu.customerid = c.customerid)
            when 'L' then
                (select cus.customercd from o9cbs.d_customer cus where cus.customerid
                = (select l.mcustomerid from o9cbs.d_ctmlkg l where l.lkgid = c.customerid))
            else 'N/A' end, 'C') as CTMID,
        c.inttn,
        case when INTTNUN='M' then
            (select (b.IFCVAL+b.MARVAL) from o9cbs.D_IFCBAL b where b.defacno=c.DEFACNO
            AND b.IFCCD IN (select ic.IFCCD from o9cbs.D_IFCLST ic WHERE ic.IFCTYPE='I' AND ic.IFCSUBTYPE='IN'))/12
        when (prtn=1 and prtnun='W' and inttn=1 and inttnun='W') then ((select (b.IFCVAL+b.MARVAL) from o9cbs.D_IFCBAL b where b.defacno=c.DEFACNO
            AND b.IFCCD IN (select ic.IFCCD from o9cbs.D_IFCLST ic WHERE ic.IFCTYPE='I' AND ic.IFCSUBTYPE='IN'))/12)/4
        when (prtn=2 and prtnun='W' and inttn=2 and inttnun='W') then ((select (b.IFCVAL+b.MARVAL) from o9cbs.D_IFCBAL b where b.defacno=c.DEFACNO
            AND b.IFCCD IN (select ic.IFCCD from o9cbs.D_IFCLST ic WHERE ic.IFCTYPE='I' AND ic.IFCSUBTYPE='IN'))/12/4)*2
        when INTTNUN='D' then (select (b.IFCVAL+b.MARVAL) from o9cbs.D_IFCBAL b where b.defacno=c.DEFACNO
        AND b.IFCCD IN (select ic.IFCCD from o9cbs.D_IFCLST ic WHERE ic.IFCTYPE='I' AND ic.IFCSUBTYPE='IN'))/360  
        end as IFCValue,
        case when INTTNUN='M' then
            (select (b.IFCVAL+b.MARVAL) from o9cbs.D_IFCBAL b where b.defacno=c.DEFACNO
            AND b.IFCCD IN (select ic.IFCCD from o9cbs.D_IFCLST ic WHERE ic.IFCTYPE='I' AND ic.IFCSUBTYPE='IN'))
        end as INTPERYEAR,
        b.BRANCHCD, 
        b.phone as BRNAME,
        substr(b.phone,1,11) as phonebranch, 
        u.UREFID as TCode, 
        u.phone as TNamem,
        b.brname as braname,
        us.cdid as CoCode,
        (select caption from o9cbs.c_cdlist cd where cd.cdgrp='CRD' and cd.cdname='CRMID' and cd.cdid=c.crmid) as CoName,
        us.cdval as CoPhone, 
        BI.LH_F_CUSTOMERNAME_KH(
            case c.ctmtype when 'C' then
                (select cu1.mname from o9cbs.d_customer cu1 where cu1.customerid = c.customerid)
            when 'L' then
                (select cu1.mname from o9cbs.d_customer cu1 where cu1.customerid =
                (select l.MCUSTOMERID from o9cbs.D_CTMLKG l WHERE l.lkgid = c.CUSTOMERID))
            else 'N/A'
            end,'C') as CNameKH,
        BI.LH_F_CUSTOMERNAME_EN(
            case c.ctmtype when 'C' then
                (select cu1.mname from o9cbs.d_customer cu1 where cu1.customerid = c.customerid)
            when 'L' then
                (select cu1.mname from o9cbs.d_customer cu1 where cu1.customerid =
                (select l.MCUSTOMERID from o9cbs.D_CTMLKG l WHERE l.lkgid = c.CUSTOMERID))
            else 'N/A'
            end,'C') as CNameEN,
        BI.LH_F_CUSTOMERNAME_KH(
            case c.ctmtype when 'C' then
                (select cu1.mname from o9cbs.d_customer cu1 where cu1.customerid = c.customerid)
            when 'L' then
                (select cu1.mname from o9cbs.d_customer cu1 where cu1.customerid =
                (select l.MCUSTOMERID from o9cbs.D_CTMLKG l WHERE l.lkgid = c.CUSTOMERID))
            else 'N/A'
            end,'N') as CNickNameKH,
        BI.LH_F_GET_CURRENCY(c.ccrcd) as ccrcd,
        (select L.CAPTION
            from o9cbs.c_cdlist l where l.cdname = 'CRDPRP'
            and l.cdid = trim(c.CRDPRP)) as CreditPP,
        BI.lh_f_credit_security_kh(c.ostype, c.gstype) as SecType,
        BI.LH_F_CUSTOMERNAME_KH(
            case c.ctmtype when 'C' then ''
            when 'L' then
                (select cu1.mname from o9cbs.d_customer cu1 where cu1.customerid =
                (select l.DCUSTOMERID from o9cbs.D_CTMLKGRL l where l.LKGID = c.CUSTOMERID and l.status in('1','2') and rownum = 1))
            else 'N/A'
            end,'C') as CPNameKH,
        BI.LH_F_CUSTOMERNAME_EN(
            case c.ctmtype when 'C' then ''
            when 'L' then
                (select cu1.mname from o9cbs.d_customer cu1 where cu1.customerid =
                (select l.DCUSTOMERID from o9cbs.D_CTMLKGRL l where l.LKGID = c.CUSTOMERID and l.status in('1','2') and rownum = 1))
            else 'N/A'
            end,'C') as CPNameEN,
        BI.LH_F_FORMAT_ACCCODE(
            case c.ctmtype when 'C' then ''
            when 'L' then
                (select cu1.customercd from o9cbs.d_customer cu1 where cu1.customerid =
                (select l.DCUSTOMERID from o9cbs.D_CTMLKGRL l where l.LKGID = c.CUSTOMERID and l.status in('1','2') and rownum = 1))
            else 'N/A'
            end,'C') as CPCode,
        BI.LH_F_CUSTOMERNAME_KH(
            case c.ctmtype when 'C' then ''
            when 'L' then
                (select cu1.mname from o9cbs.d_customer cu1 where cu1.customerid =
                (select l.DCUSTOMERID from o9cbs.D_CTMLKGRL l where l.LKGID = c.CUSTOMERID and rownum = 1))
            else 'N/A'
            end,'N') as CPNickName,
        BI.LH_F_GET_DEPOSITACC_BY_DEFNO(c.DEFACNO) as DepositAcc, 
        to_char((c.opndt), 'DD/MM/YY') as DisburseDt,
        to_char((select max(ch.duedt) from o9cbs.D_CRSCHD ch where ch.rptype='P' and ch.defacno=c.defacno),'DD/MM/YY') as MaturityDt,
        BI.lh_f_customer_address_kh_name(c.CUSTOMERID, c.ctmtype) as CTMAddress,
        to_char(sysdate, 'dd-MM-yyyy HH:MI AM') as CuDate,
        case c.ctmtype when 'C' then
            (SELECT CUSTOMERCD FROM o9cbs.D_CUSTOMER CT WHERE CT.CUSTOMERID=C.CUSTOMERID)
        when 'L' then
            (select BI.LH_F_FORMAT_ACCCODE(l.lkgcd, 'C') from o9cbs.D_CTMLKG l WHERE l.lkgid = c.CUSTOMERID)
        end as LINKAGECODE,
        case c.ctmtype when 'C' then
            (SELECT o9cbs.o9util.get_flat_json_value(ct.mphone,'H')phone FROM o9cbs.D_CUSTOMER CT WHERE CT.CUSTOMERID=C.CUSTOMERID)
        when 'L' then
            (select o9cbs.o9util.get_flat_json_value(a1.mphone,'H')phone from o9cbs.d_customer a1 where a1.customerid= (select mcustomerid from o9cbs.d_ctmlkg a1 where c.customerid= a1.lkgid))
        end as CTMPHONE,
        ROUND(c.prvtn/12,2) as prvpermonth,
        c.prvtn as prvperyear,
        (select ifcval/12 from o9cbs.d_ifcbal a where a.ifccd='788' and c.defacno=a.defacno) as pen,
        CASE WHEN INTMODE='C' THEN 'Annuity'
        WHEN INTMODE='F' THEN 'Declining' end as TYPE,
        loancycle
    FROM o9cbs.D_CREDIT c
    INNER JOIN (
        with g as (
            SELECT v.DEFACNO,
            \"TO_CHAR\"(v.DUEDT,'DD-MM-YYYY') as DUEDATE,
            sum(v.Interest) as Interest, 
            sum(v.principal) as Principal,
            (sum(v.Interest) + sum(v.principal)) as TotalPaid
            FROM (
                SELECT o9cbs.D_CREDIT.DEFACNO,
                    o9cbs.D_CREDIT.ACNO,
                    o9cbs.D_CRSCHD.DUENO,
                    o9cbs.D_CRSCHD.DUEDT,
                    o9cbs.D_CRSCHD.AMT as Interest,
                    o9cbs.D_CRSCHD.PAID as PaidInt, 
                    0 as Principal, 
                    0 as PaidPrin,
                    o9cbs.D_CREDIT.INTMODE,
                    o9cbs.D_CRSCHD.RPTYPE
                FROM o9cbs.D_CREDIT
                INNER JOIN o9cbs.D_CRSCHD
                ON o9cbs.D_CREDIT.DEFACNO = o9cbs.D_CRSCHD.DEFACNO
                WHERE o9cbs.D_CREDIT.INTMODE in ('C','L') and o9cbs.D_CRSCHD.RPTYPE='I'
                AND o9cbs.D_CREDIT.ACNO = :acno
                UNION ALL
                SELECT o9cbs.D_CREDIT.DEFACNO,
                    o9cbs.D_CREDIT.ACNO, 
                    o9cbs.D_CRSCHDEST.DUENO,
                    o9cbs.D_CRSCHDEST.DUEDT,
                    o9cbs.D_CRSCHDEST.AMT,
                    o9cbs.D_CRSCHDEST.PAID, 
                    0 as Principal, 
                    0 as PaidPrin,
                    o9cbs.D_CREDIT.INTMODE,
                    o9cbs.D_CRSCHDEST.RPTYPE 
                FROM o9cbs.D_CRSCHDEST
                INNER JOIN o9cbs.D_CREDIT
                ON o9cbs.D_CREDIT.DEFACNO = o9cbs.D_CRSCHDEST.DEFACNO
                WHERE o9cbs.D_CREDIT.INTMODE = 'F' and o9cbs.D_CRSCHDEST.RPTYPE='E'
                AND o9cbs.D_CREDIT.ACNO = :acno
                UNION ALL
                select s.defacno, c.acno, s.dueno, s.duedt, 0, 0,
                    s.amt, s.paid, c.intmode, s.rptype
                from o9cbs.d_crschd s
                inner join o9cbs.d_credit c on s.defacno = c.defacno
                where s.rptype = 'P'
                AND c.acno = :acno
            ) v
            group by v.DEFACNO, \"TO_CHAR\"(v.DUEDT,'DD-MM-YYYY'), v.DUEDT
            HAVING (sum(v.Interest) + sum(v.principal)) != 0
            order by v.defacno, v.DUEDT
        )
        select g.DEFACNO, 
            rownum as DUENo, 
            g.DUEDATE, 
            c.frdt as CreditDT,
            CASE rownum WHEN MIN(ROWNUM) OVER (PARTITION BY G.DEFACNO) THEN
                \"TO_DATE\"(g.DUEDATE, 'DD-MM-YYYY') - c.frdt
            ELSE
                TRUNC(\"TO_DATE\"(g.DUEDATE, 'DD-MM-YYYY')) -
                to_date(Lag(g.DUEDATE, 1) over (Partition by g.defacno order by rownum), 'DD-MM-YY')
            END as Days,
            (\"SUM\"(nvl(g.Principal,0)) OVER (PARTITION by g.DEFACNO ORDER BY g.DEFACNO)
            - \"SUM\"(nvl(g.Principal,0)) OVER (PARTITION by g.DEFACNO ORDER BY rownum)) as BL,
            g.Interest, 
            g.Principal,
            nvl(fee.amt,0) as feeamt, 
            nvl(g.Principal + g.Interest+fee.amt,0) as TotalPaid,
            (select ifcbal.ifcval+ifcbal.marval from o9cbs.d_ifcbal ifcbal where ifcbal.defacno=c.defacno and ifcbal.ifccd in (select ifccd from o9cbs.d_ifclst where ifctype='I' and ifcsubtype='IM'))/12 as AdminFeeRate
        from g
        inner join o9cbs.d_credit c on g.DEFACNO = c.DEFACNO
        left join (select defacno,dueno,TO_CHAR(DUEDT,'DD-MM-YYYY') duedt,amt From o9cbs.d_crschdest where rptype='F') fee on g.defacno= fee.defacno and g.duedate= fee.DUEDT
        where c.acno = :acno
        order by g.DEFACNO, \"TO_DATE\"(g.DUEDATE, 'DD-MM-YYYY')
    ) v on v.DEFACNO = c.DEFACNO
    inner join o9cbs.S_BRANCH b on c.BRANCHID = b.BRANCHID
    INNER JOIN o9cbs.S_USRAC u on c.USRID = u.USRID
    LEFT JOIN o9cbs.C_CDLIST us on cast(c.crmid as varchar2(20)) = us.cdid and us.cdname = 'CRMID'
    WHERE c.ACNO = :acno
    ORDER BY v.DUENO";
    
    $scheduleRows = oracleFetchAll($sql, ['acno' => $acno]);
    
    // Calculate summary totals
    foreach ($scheduleRows as $row) {
        if (isset($row['period']) && is_numeric($row['period'])) {
            $summary['totalDays'] += (int)$row['period'];
        }
        if (isset($row['principal']) && is_numeric($row['principal'])) {
            $summary['totalPrincipal'] += (float)$row['principal'];
        }
        if (isset($row['interest']) && is_numeric($row['interest'])) {
            $summary['totalInterest'] += (float)$row['interest'];
        }
        if (isset($row['totalamount']) && is_numeric($row['totalamount'])) {
            $summary['totalAmount'] += (float)$row['totalamount'];
        }
    }
    
    // Get initial balance (first row's BL + first PRINCIPAL)
    if (!empty($scheduleRows) && isset($scheduleRows[0]['bl']) && is_numeric($scheduleRows[0]['bl'])) {
        $firstPrincipal = isset($scheduleRows[0]['principal']) && is_numeric($scheduleRows[0]['principal']) 
            ? (float)$scheduleRows[0]['principal'] 
            : 0;
        $firstBl = (float)$scheduleRows[0]['bl'];
        $summary['initialBalance'] = $firstBl + $firstPrincipal;
    }
    
    // Format dates and numbers for JSON response
    $formattedRows = [];
    foreach ($scheduleRows as $row) {
        $formattedRows[] = [
            'dueno' => $row['dueno'] ?? '',
            'dayname' => $row['dayname'] ?? '',
            'duedate' => formatScheduleDateForJson($row['duedate'] ?? ''),
            'period' => $row['days'] ?? '',
            'principal' => $row['principal'] ?? '',
            'interest' => $row['interest'] ?? '',
            'totalamount' => $row['totalamount'] ?? '',
            'bl' => $row['bl'] ?? ''
        ];
    }
    
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'data' => [
            'schedule' => $formattedRows,
            'summary' => $summary
        ]
    ]);
    
} catch (Exception $e) {
    $scheduleError = $e->getMessage();
    logError('Failed to fetch loan schedule', [
        'error' => $e->getMessage(),
        'acno' => $acno,
        'file' => __FILE__,
        'line' => $e->getLine()
    ]);
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Failed to fetch loan schedule',
        'error' => $scheduleError
    ]);
}

/**
 * Format date for JSON response (DD-MM-YY format)
 */
function formatScheduleDateForJson($dateString) {
    if (empty($dateString) || $dateString === null || $dateString === '') {
        return '';
    }
    try {
        $timestamp = strtotime($dateString);
        if ($timestamp === false) {
            return $dateString;
        }
        $day = str_pad(date('d', $timestamp), 2, '0', STR_PAD_LEFT);
        $month = str_pad(date('m', $timestamp), 2, '0', STR_PAD_LEFT);
        $year = substr(date('Y', $timestamp), -2);
        return "{$day}-{$month}-{$year}";
    } catch (Exception $e) {
        return $dateString;
    }
}
