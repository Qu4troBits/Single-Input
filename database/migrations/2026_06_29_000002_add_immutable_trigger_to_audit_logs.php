<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class() extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Create function to prevent UPDATE and DELETE on audit_logs
        DB::statement(<<<'SQL'
            CREATE OR REPLACE FUNCTION prevent_audit_logs_modification()
            RETURNS TRIGGER AS $$
            BEGIN
                IF TG_OP = 'UPDATE' THEN
                    RAISE EXCEPTION 'UPDATE operation not allowed on audit_logs table. Audit records are immutable.';
                ELSIF TG_OP = 'DELETE' THEN
                    RAISE EXCEPTION 'DELETE operation not allowed on audit_logs table. Audit records are immutable.';
                END IF;
                RETURN NULL;
            END;
            $$ LANGUAGE plpgsql
        SQL);

        // Create trigger for UPDATE operations
        DB::statement(<<<'SQL'
            CREATE TRIGGER audit_logs_prevent_update
                BEFORE UPDATE ON audit_logs
                FOR EACH ROW
                EXECUTE FUNCTION prevent_audit_logs_modification()
        SQL);

        // Create trigger for DELETE operations
        DB::statement(<<<'SQL'
            CREATE TRIGGER audit_logs_prevent_delete
                BEFORE DELETE ON audit_logs
                FOR EACH ROW
                EXECUTE FUNCTION prevent_audit_logs_modification()
        SQL);

        // Add comment to table explaining immutability
        DB::statement(<<<'SQL'
            COMMENT ON TABLE audit_logs IS 'Audit logs table - records are immutable. UPDATE and DELETE operations are prohibited via database triggers.'
        SQL);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop triggers
        DB::statement('DROP TRIGGER IF EXISTS audit_logs_prevent_update ON audit_logs');
        DB::statement('DROP TRIGGER IF EXISTS audit_logs_prevent_delete ON audit_logs');

        // Drop function
        DB::statement('DROP FUNCTION IF EXISTS prevent_audit_logs_modification()');

        // Remove comment
        DB::statement('COMMENT ON TABLE audit_logs IS NULL');
    }
};
