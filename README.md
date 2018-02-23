extension-tao-outcomerdf
========================

implements resultServer interface to store results using ontology/statements table

### Registering default (phpfile) result page cache
```bash
 $ sudo -u www-data php index.php '\oat\taoOutcomeUi\scripts\tools\RegisterDefaultResultCache'
```

### Delete result cache for a delivery execution aka. result
```bash
 $ sudo -u www-data php index.php 'oat\taoOutcomeUi\scripts\tools\DeleteResultCache' -u {deliveryExecutionUri}
```