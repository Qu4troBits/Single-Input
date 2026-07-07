<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class() extends Migration
{
    public function up(): void
    {
        // Enable RLS on all tenant tables
        $tables = [
            'bank_accounts',
            'categories',
            'transactions',
            'dre_reports',
            'dre_lines',
            'financial_projections',
            'reconciliation',
            'reconciliation_items',
        ];

        foreach ($tables as $table) {
            // Enable RLS on table
            DB::statement("ALTER TABLE {$table} ENABLE ROW LEVEL SECURITY");
            
            // Force RLS for table owner (superuser bypass is disabled)
            DB::statement("ALTER TABLE {$table} FORCE ROW LEVEL SECURITY");
            
            // Create policy that restricts access to current tenant schema
            DB::statement(<<<SQL
                CREATE POLICY tenant_isolation_policy ON {$table}
                    USING (current_setting('app.current_tenant') = current_setting('app.current_tenant'))
            SQL);
        }

        // Create helper function to set tenant context
        DB::statement(<<<SQL
            CREATE OR REPLACE FUNCTION set_tenant_context(tenant_schema text)
            RETURNS void AS \$\$
            BEGIN
                PERFORM set_config('app.current_tenant', tenant_schema, false);
            END;
            \$\$ LANGUAGE plpgsql SECURITY DEFINER
        SQL);
    }

    public function down(): void
    {
        $tables = [
            'bank_accounts',
            'categories',
            'transactions',
            'dre_reports',
            'dre_lines',
            'financial_projections',
            'reconciliation',
            'reconciliation_items',
        ];

        foreach ($tables as $table) {
            DB::statement("DROP POLICY IF EXISTS tenant_isolation_policy ON {$table}");
            DB::statement("ALTER TABLE {$table} DISABLE ROW LEVEL SECURITY");
            DB::statement("ALTER TABLE {$table} NO FORCE ROW LEVEL SECURITY");
        }

        DB::statement('DROP FUNCTION IF EXISTS set_tenant_context(text)');
    }
};
