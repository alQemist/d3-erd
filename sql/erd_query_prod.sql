
SELECT de.entity_name as entity,
       de.entity_name_singular as entity_key,
       de.id as entity_id,
       de.x as fixed_x,
       de.y as fixed_y,
       (de.physical-0) as physical,
       (de.status-0) as status,
       de.description,
       GROUP_CONCAT(dd.status,":",dd.data_name,":",replace(dd.description,",","|"),":",CASE WHEN dd.relationship <> "" THEN dd.relationship ELSE "" END ) as items,
       d.domain_name as domain
FROM data_entity de
         LEFT JOIN data_dictionary dd on de.id = dd.entity_id AND dd.status = "1"
         LEFT JOIN domain d on de.domain_id = d.id
WHERE de.status = "1"
GROUP BY entity

