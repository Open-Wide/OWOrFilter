<?php
/**
 * @author Simon Boyer
 * from a work of Bertrand Dunogier and Grégory Becue
 * 2011
 */
class ORExtendedFilter
{

function CreateSqlParts( $params )
  {
    $tables = array();
    $joins  = array();
    $joinsResult  = array();
	$mode = 'AND';

    // foreach filtered attribute, we add a join the relation table and filter
    // on the attribute ID + object ID
    foreach( $params as $param )
    {
        if ( !is_array( $param ) ) {
			if (strtoupper($param) == 'OR') {
				$mode = strtoupper($param);
			}
			continue;
		}
		$joins  = array();

        if ( !is_numeric( $param[0] ) )
        {
            $classAttributeId = eZContentObjectTreeNode::classAttributeIDByIdentifier( $param[0] );
        }
        else
        {
            $classAttributeId = $param[0];
        }

        // multiple objects ids
        if ( is_array($param[1]) )
        {

	    // Treatment for 'and' parameters
            if($param[2] == 'and')
            {            	
				foreach( $param[1] as $objectId )
				{
					if ( is_numeric( $objectId ) )
					{
					    $tableName = 'orfilter_link'. $objectId;
					    $tables[] = 'ezcontentobject_link ' . $tableName;
					
					    $joins[]  = $tableName . '.from_contentobject_id = ezcontentobject.id';
					    $joins[]  = $tableName . '.from_contentobject_version = ezcontentobject.current_version';
					    $joins[]  = $tableName . '.contentclassattribute_id = ' . $classAttributeId;
					    $joins[]  = $tableName . '.to_contentobject_id = ' . $objectId.'
';
					}
				}
	     }
	     elseif($param[2] == 'or') 
	     {
	     	// Treatment for 'or' parameters
	    	$cpt = 0;
			$chaineCritere = "(";
			foreach( $param[1] as $objectId )
			{
				if ( is_numeric( $objectId ) )
				{
					if($cpt == 0)
					{	
						$tableName = 'orfilter_link'. $objectId;
						$tables[] = 'ezcontentobject_link ' . $tableName;
						
						$joins[]  = $tableName . '.from_contentobject_id = ezcontentobject.id';
						$joins[]  = $tableName . '.from_contentobject_version = ezcontentobject.current_version';
						$joins[]  = $tableName . '.contentclassattribute_id = ' . $classAttributeId;
						
						$chaineCritere .= $tableName . '.to_contentobject_id = ' . $objectId;
					}
				    	else
				    	{
				    		$chaineCritere .= ' or '.$tableName . '.to_contentobject_id = ' . $objectId;	
				    	}	
				}
				
				$cpt++;
			}	     	
	     	
	     	$joins[]  = $chaineCritere.")";
	     }

        }


        // single object id
        else
        {
            $objectId = $param[1];

            $tableName = 'orfilter_link'. $objectId;
            $tables[] = 'ezcontentobject_link ' . $tableName;

            $joins[]  = $tableName . '.from_contentobject_id = ezcontentobject.id';
            $joins[]  = $tableName . '.from_contentobject_version = ezcontentobject.current_version';
            $joins[]  = $tableName . '.contentclassattribute_id = ' . $classAttributeId;
            $joins[]  = $tableName . '.to_contentobject_id = ' . $objectId;
        }
		$joinsResult[] =  "(\n".implode( " AND\n ", $joins ) . "\n)\n\n";

    }

    if ( !count( $tables ) or !count( $joins ) or !count( $joinsResult ) )
    {
      $tables = $joins = $joinsResult = '';
    }
    else
    {
      $tables = array_unique($tables);
      $tables = "\n, "    . implode( "\n, ", $tables );
      $joinsResult =  "(\n".implode( " $mode\n ", $joinsResult ) . "\n) AND\n ";
    }
    
    return array( 'tables' => $tables, 'joins' => $joinsResult );
  }
  
}
