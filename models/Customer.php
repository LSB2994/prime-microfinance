<?php
/**
 * Customer model
 *
 * Encapsulates data access for customer list and customer detail using Oracle.
 */

require_once __DIR__ . '/../includes/db.php';

class Customer
{
    /**
     * Get all customers from the bi.ctm_infor_v1 view.
     *
     * @return array
     * @throws Exception
     */
    public static function getAll(): array
    {
        $sql = "SELECT * FROM bi.ctm_infor_v1";
        return oracleFetchAll($sql);
    }

    /**
     * Get a single customer row by ACNO from the bi.ctm_infor_v1 view.
     *
     * @param string $acno
     * @return array|null
     * @throws Exception
     */
    public static function getByAcno(string $acno): ?array
    {
        $sql = "SELECT * FROM bi.ctm_infor_v1 WHERE acno = :acno";
        $rows = oracleFetchAll($sql, [':acno' => $acno]);
        return $rows[0] ?? null;
    }
}


