# d3-erd
 Entity Relationship Diagram written in d3.v4
 Based on Mike Bostock's Force Dragging 1 example https://bl.ocks.org/mbostock/2675ff61ea5e063ede2b5d63c08020c7

Designed to produce a data-driven Entity Relationship Diagram from a Data Dictionary.

This solution was just one facet of a larger Enterprise Architecture project undertaken to model and document the business domains and their relationships to the data architecture.

The Data Dictionary was used as the source of data driving the ERD. Whatever the data source the model should be as follows:

	   entity:      name of entity (can be singular or plural)

	   entity_key:  singular name of entity  (must be singular as its used to create the relationships)

	   entity_id:   unique entity id

	   *fixed_x:     x position as a decimal (percentage),

	   *fixed_y:     y position as a decimal (percentage),

	   physical:    boolean indicator that data is physical or not,

	   status":     boolean indicator that entity is approved for release,

	   description: description of the data used for tooltips,

	   items:       array of columns under each entity with these elements delimited by ":"
   
                status:boolean indicator that data is approved for release,
                
                name:column name
                
                description: column description
                
                relationship: optional, one of the following values
		
				11 - One and only One
				1M - One or Many
				M - Many
				01 - Zero or One
				0M - Zero or Many
				arrow - included for miscellaneous use
                              
   domain:      name of domain the entity is under
   
	   Sample JSON
	  {
	    "entity": "assessments",
	    "entity_key": "assessment",
	    "entity_id": "16",
	    "fixed_x": "0.638562",
	    "fixed_y": "0.291789",
	    "physical": "1",
	    "status": "0",
	    "description": null,
	    "items": "1:id:id for the assessment record:,1:request_id:id for the request record:1M",
	    "domain": "Clinical Assessments"
	  }

* NOTE: fixed_x and fixed_y values are ignored if the setting below is 0, this allows the forceDirected layout to "run".
If is_fixed = 1 then the entity (nodes) will be in fixed positions. 

		var is_fixed = 0 

DRAGGING AND SAVING NEW NODE POSITIONS:
Saving new positions for dragged nodes happens automatically if the api.js is used. Otherwise its not possible using static json file as a datasource.

