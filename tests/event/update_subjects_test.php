<?php
/**
 *
 * Smart Subjects
 *
 * @copyright (c) 2015 Matt Friedman
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace vse\smartsubjects\tests\event;

class update_subjects_test extends listener_base
{
	/**
	 * Two models of event data to use in the tests
	 *
	 * @return array
	 */
	protected function import_topic_data()
	{
		return array(
			1 => array(
				'mode' 				=> 'edit',
				'update_subject'	=> true,
				'post_id'			=> 1,
				'topic_id'			=> 1,
				'forum_id'			=> 1,
				'data'				=> array(
					'topic_title'			=> 'Test Topic 1',
					'topic_first_post_id'	=> 1,
					'topic_last_post_id'	=> 3,
				),
				'post_data' 		=> array(
					'post_subject'	=> 'New Topic Title 1',
				),
			),

			2 => array(
				'mode' 				=> 'edit',
				'update_subject'	=> true,
				'post_id'			=> 4,
				'topic_id'			=> 2,
				'forum_id'			=> 1,
				'data'				=> array(
					'topic_title'			=> 'Test Topic 2',
					'topic_first_post_id'	=> 4,
					'topic_last_post_id'	=> 6,
				),
				'post_data' 		=> array(
					'post_subject'	=> 'New Topic Title 2',
				),
			),
		);
	}

	/**
	 * Test data for test_update_subjects
	 *
	 * @return array
	 */
	public function update_subjects_test_data()
	{
		$data = $this->import_topic_data();

		return array(
			array(
				// standard title update
				$data[1],
				array(
					array('f_smart_subjects', 1, true),
				),
				false,
				array(
					array('post_id' => 2, 'post_subject' => 'Re: New Topic Title 1'),
					array('post_id' => 3, 'post_subject' => 'Re: New Topic Title 1'),
				),
			),
			array(
				// standard title update
				$data[2],
				array(
					array('f_smart_subjects', 1, true),
				),
				false,
				array(
					array('post_id' => 5, 'post_subject' => 'Re: New Topic Title 2'),
					array('post_id' => 6, 'post_subject' => 'Custom Post Title'),
				),
			),
			array(
				// update with overwrite mode on
				$data[2],
				array(
					array('f_smart_subjects', 1, true),
				),
				true,
				array(
					array('post_id' => 5, 'post_subject' => 'Re: New Topic Title 2'),
					array('post_id' => 6, 'post_subject' => 'Re: New Topic Title 2'),
				),
			),
			array(
				// not editing a post
				array_merge($data[1], array('mode' => 'post')),
				array(
					array('f_smart_subjects', 1, true),
				),
				false,
				array(
					array('post_id' => 2, 'post_subject' => 'Re: Test Topic 1'),
					array('post_id' => 3, 'post_subject' => 'Re: Test Topic 1'),
				),
			),
			array(
				// not updating a title
				array_merge($data[1], array('update_subject' => false)),
				array(
					array('f_smart_subjects', 1, true),
				),
				false,
				array(
					array('post_id' => 2, 'post_subject' => 'Re: Test Topic 1'),
					array('post_id' => 3, 'post_subject' => 'Re: Test Topic 1'),
				),
			),
			array(
				// not editing the first post post
				array_merge($data[1], array('post_id' => 2)),
				array(
					array('f_smart_subjects', 1, true),
				),
				false,
				array(
					array('post_id' => 1, 'post_subject' => 'Test Topic 1'),
					array('post_id' => 3, 'post_subject' => 'Re: Test Topic 1'),
				),
			),
			array(
				// unauthorized forum
				$data[1],
				array(
					array('f_smart_subjects', 2, false),
				),
				false,
				array(
					array('post_id' => 2, 'post_subject' => 'Re: Test Topic 1'),
					array('post_id' => 3, 'post_subject' => 'Re: Test Topic 1'),
				),
			),

		);
	}

	/**
	 * Test the update_subjects method, check expected post subjects
	 *
	 * @dataProvider update_subjects_test_data
	 * @param $data
	 * @param $permissions
	 * @param $overwrite
	 * @param $expected
	 */
	public function test_update_subjects($data, $permissions, $overwrite, $expected)
	{
		// Set permission variable
		$this->auth->expects($this->any())
			->method('acl_get')
			->with($this->stringContains('_'), $this->anything())
			->will($this->returnValueMap($permissions));

		// Set request variable
		$this->request->expects($this->any())
			->method('is_set_post')
			->with($this->equalTo('overwrite_subjects'))
			->will($this->returnValue($overwrite));

		// Define the event object
		$event = new \phpbb\event\data($data);

		// Set the listener object
		$this->set_listener();

		// Perform update subjects
		$this->listener->update_subjects($event);

		// Get the reply subjects now in the db
		$result = $this->db->sql_query('SELECT post_id, post_subject
			FROM phpbb_posts
			WHERE topic_id = ' . (int) $data['topic_id'] . '
				AND post_id <> ' . (int) $data['post_id'] . '
			ORDER BY post_id');
		$this->assertEquals($expected, $this->db->sql_fetchrowset($result));
		$this->db->sql_freeresult($result);
	}

	/**
	 * Test data for test_update_forum_subject
	 *
	 * @return array
	 */
	public function update_forum_subject_test_data()
	{
		$data = $this->import_topic_data();

		return array(
			array($data[1], 'Re: New Topic Title 1'), // forum subject is updated
			array($data[2], 'Re: Test Topic 1'), // forum subject is not updated
		);
	}

	/**
	 * Test the update_subjects method, check expected forum last post subject
	 *
	 * @dataProvider update_forum_subject_test_data
	 * @param $data
	 * @param $expected
	 */
	public function test_update_forum_subject($data, $expected)
	{
		// Set permission variable
		$this->auth->expects($this->any())
			->method('acl_get')
			->with($this->stringContains('f_smart_subjects'), $this->anything())
			->will($this->returnValue(true));

		// Define the event object
		$event = new \phpbb\event\data($data);

		// Set the listener object
		$this->set_listener();

		// Perform update subjects
		$this->listener->update_subjects($event);

		// Get the last forum reply subject now in the db
		$result = $this->db->sql_query('SELECT forum_last_post_subject
			FROM phpbb_forums
			WHERE forum_id = ' . (int) $data['forum_id']);
		$this->assertEquals($expected, $this->db->sql_fetchfield('forum_last_post_subject'));
		$this->db->sql_freeresult($result);
	}
}
