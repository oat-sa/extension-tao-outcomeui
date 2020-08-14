CREATE TABLE IF NOT EXISTS result_table (
	col_test_taker_id VARCHAR(16000),
	col_compilation_time INT,
	col_field_without_type VARCHAR(16000),
	col_tets_variable_grade_column VARCHAR(16000),
	col_tets_variable_response_column VARCHAR(16000)
);

INSERT INTO result_table (
	   col_test_taker_id,
	   col_compilation_time,
	   col_field_without_type,
	   col_tets_variable_grade_column,
	   col_tets_variable_response_column
) VALUES (
	   'http://nec-pr.docker.localhost/tao.rdf#i5f16bd028eb6e202ad4b5d184"f67e22',
	   1594828375,
	   '12345'
	),
	(
	   'http://nec-pr.docker.localhost/tao.rdf#i5f16bd028eb6e202ad4b5d43f67e24',
	   1594828388,
	   '33333'
	),
	(
	   'http://nec-pr.docker.localhost/tao.rdf#i5f16bd028eb6ee202ae213123121231',
	   159482838228,
	   '3311333'
	),
	(
	   'http://nec-pr.docker.localhost/tao.rdf#i5f16bd028eb6ee202ae11111',
	   1594838228,
	   '331111'
	);