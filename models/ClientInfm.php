<?php
/**
 * ClientInfm model
 *
 * Provides access to BI.CLIENT_BACKDATE view.
 */

require_once __DIR__ . '/../includes/db.php';

class ClientInfm
{
    /**
     * Get all client backdate rows.
     *
     * @return array
     * @throws Exception
     */
    public static function getAll(): array
    {
        $sql = "
            SELECT
                VALUEDT,
                BRANCHID,
                BR_ID,
                BR_NAME,
                S_MICRO_B_KHR,
                S_MICRO_B_USD,
                S_SMALL_B_KHR,
                S_SMALL_B_USD,
                S_STAFF_LOAN_KHR,
                S_STAFF_LOAN_USD,
                S_TOTAL,
                L_MICRO_B_KHR,
                L_MICRO_B_USD,
                L_SMALL_B_KHR,
                L_SMALL_B_USD,
                L_STAFF_LOAN_KHR,
                L_STAFF_LOAN_USD,
                L_TOTAL,
                TOTAL
            FROM BI.CLIENT_BACKDATE
        ";

        return oracleFetchAll($sql);
    }
}


