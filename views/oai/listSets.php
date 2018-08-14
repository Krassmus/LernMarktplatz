<?= '<?xml version="1.0" encoding="UTF-8"?>' ?>
<OAI-PMH xmlns="http://www.openarchives.org/OAI/2.0/" 
         xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
         xsi:schemaLocation="http://www.openarchives.org/OAI/2.0/
         http://www.openarchives.org/OAI/2.0/OAI-PMH.xsd">
         <responseDate><?= $currentDate ?></responseDate>
         <request verb=<?='"'.$verb.'"' ?>><?= $task_repo = htmlReady(Request::url()) ?></request>
 <ListSets>
 <? foreach ($tags as $tag) : ?>
  <set>
    <setSpec><?= $tag->id ?></setSpec>
    <setName><?= $tag->name ?></setName>
  </set>
<? endforeach ?>
 
 </ListSets>
</OAI-PMH>