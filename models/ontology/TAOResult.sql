

INSERT INTO `models` (`modelID`, `modelURI`, `baseURI`) VALUES
(13, 'http://www.tao.lu/Ontologies/TAOResult.rdf', 'http://www.tao.lu/Ontologies/TAOResult.rdf#');


INSERT INTO `statements` (`modelID`, `subject`, `predicate`, `object`, `l_language`, `author`, `stread`, `stedit`, `stdelete`) VALUES
(8, 'http://127.0.0.1/middleware/demo.rdf#i1258986804008574400', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#type', 'http://www.w3.org/2000/01/rdf-schema#Class', 'en', 'demo', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]'),
(8, 'http://127.0.0.1/middleware/demo.rdf#i1258986804008574400', 'http://www.w3.org/2000/01/rdf-schema#label', 'Result_1', '', 'demo', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]'),
(8, 'http://127.0.0.1/middleware/demo.rdf#i1258986804008574400', 'http://www.w3.org/2000/01/rdf-schema#comment', 'Result_1 created from taoResults_models_classes_ResultsService the 2009-11-23 02:33:24', '', 'demo', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]'),
(8, 'http://127.0.0.1/middleware/demo.rdf#i1258986804008574400', 'http://www.w3.org/2000/01/rdf-schema#subClassOf', 'http://www.tao.lu/Ontologies/TAOResult.rdf#Result', '', 'demo', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]'),
(8, 'http://127.0.0.1/middleware/demo.rdf#i1258987961098258200', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#type', 'http://www.tao.lu/Ontologies/TAOResult.rdf#Result', 'en', 'demo', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]'),
(8, 'http://127.0.0.1/middleware/demo.rdf#i1258987961098258200', 'http://www.w3.org/2000/01/rdf-schema#label', 'my result set', '', 'demo', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]'),
(8, 'http://127.0.0.1/middleware/demo.rdf#i1258987961098258200', 'http://www.w3.org/2000/01/rdf-schema#comment', 'Result_1 created from taoResults_models_classes_ResultsService the 2009-11-23 02:52:41', '', 'demo', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]');



INSERT INTO `statements` (`modelID`, `subject`, `predicate`, `object`, `l_language`, `author`, `stread`, `stedit`, `stdelete`) VALUES
(13, 'http://www.tao.lu/Ontologies/TAOResult.rdf#Result', 'http://www.w3.org/2000/01/rdf-schema#label', 'Result', 'EN', 'demo', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]'),
(13, 'http://www.tao.lu/Ontologies/TAOResult.rdf#Result', 'http://www.w3.org/2000/01/rdf-schema#comment', 'Result', 'EN', 'demo', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]'),
(13, 'http://www.tao.lu/Ontologies/TAOResult.rdf#Result', 'http://www.w3.org/2000/01/rdf-schema#subClassOf', 'http://www.tao.lu/Ontologies/TAO.rdf#TAOObject', '', 'demo', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]'),
(13, 'http://www.tao.lu/Ontologies/TAOResult.rdf', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#type', 'http://www.tao.lu/Ontologies/generis.rdf#Model', '', 'demo', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]'),
(13, 'http://www.tao.lu/Ontologies/TAOResult.rdf', 'http://www.w3.org/2000/01/rdf-schema#label', 'TAO Result Model', 'EN', 'demo', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]'),
(13, 'http://www.tao.lu/Ontologies/TAOResult.rdf', 'http://www.w3.org/2000/01/rdf-schema#comment', 'TAO Result Model', 'EN', 'demo', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]'),
(13, 'http://www.tao.lu/Ontologies/TAOResult.rdf', 'http://www.tao.lu/Ontologies/generis.rdf#Plugin', 'TLAresults', '', 'demo', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]'),
(13, 'http://www.tao.lu/Ontologies/TAOResult.rdf', 'http://www.tao.lu/Ontologies/generis.rdf#Plugin', 'uploadresultserver', '', 'demo', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]'),
(13, 'http://www.tao.lu/Ontologies/TAOResult.rdf', 'http://www.tao.lu/Ontologies/generis.rdf#Plugin', 'hypergraph', '', 'demo', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]'),
(13, 'http://www.tao.lu/Ontologies/TAOResult.rdf#ResultContent', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#type', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#Property', '', 'demo', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]'),
(13, 'http://www.tao.lu/Ontologies/TAOResult.rdf#ResultContent', 'http://www.w3.org/2000/01/rdf-schema#label', 'ResultContent', 'EN', 'demo', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]'),
(13, 'http://www.tao.lu/Ontologies/TAOResult.rdf#ResultContent', 'http://www.w3.org/2000/01/rdf-schema#comment', 'ResultContent', 'EN', 'demo', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]'),
(13, 'http://www.tao.lu/Ontologies/TAOResult.rdf#ResultContent', 'http://www.w3.org/2000/01/rdf-schema#domain', 'http://www.tao.lu/Ontologies/TAOResult.rdf#Result', '', 'demo', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]'),
(13, 'http://www.tao.lu/Ontologies/TAOResult.rdf#ResultContent', 'http://www.w3.org/2000/01/rdf-schema#range', 'http://www.w3.org/2000/01/rdf-schema#Literal', '', 'demo', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]'),
(13, 'http://www.tao.lu/Ontologies/TAOResult.rdf#ResultContent', 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#widget', 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#TextBox', '', 'demo', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]');
