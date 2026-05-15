<?php

declare(strict_types=1);

return [
    'ALTER TABLE candidate_unlocks
     ADD CONSTRAINT fk_candidate_unlocks_transaction
     FOREIGN KEY (transaction_id) REFERENCES wallet_transactions(id) ON DELETE RESTRICT',
];
