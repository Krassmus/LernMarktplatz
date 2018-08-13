<?= '<?xml version="1.0" encoding="UTF-8"?>' ?>
<OAI-PMH xmlns="http://www.openarchives.org/OAI/2.0/" 
  xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
  xsi:schemaLocation="http://www.openarchives.org/OAI/2.0/
  http://www.openarchives.org/OAI/2.0/OAI-PMH.xsd">
  <responseDate><?= $currentDate ?></responseDate>
  <request verb=<?='"'.$verb.'"' ?> from=<?= '"'.$currentDate.'"' ?> 
    identifier=<?= '"'.$metadataPrefix.'"' ?> set=<?= '"'.$set.'"' ?>> 
    <?= $task_repo = htmlReady(Request::url()) ?> 
  </request>
  <ListIdentifiers>
  <? foreach ($records as $record) : ?>
    <header>
        <identifier><?= $record->name."-".$record->id ?></identifier>
        <datestamp> <?= $record->mkdate ?> </datestamp>
        <setSpec> <?= $set ?> </setSpec>
      </header>
  <? endforeach ?>
   
 </ListIdentifiers>
</OAI-PMH>