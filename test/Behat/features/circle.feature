Feature: The snake circles a ring a quarter of the board in from the walls
  In order to leave a wall of body for other snakes to run into
  As the TechBash snake
  I follow a clockwise ring, eat food next to my head, and cut inward when the ring is blocked

  Scenario: Food next to the head beats the ring
    Given the board is:
      """
      -----------
      -----------
      -----------
      -----------
      -----------
      -0S--------
      --t--------
      --t--------
      --T--------
      -----------
      -----------
      """
    When the snake receives a "/start" request
    And the snake receives a "/move" request
    Then the snake moves "left"

  Scenario: Food across the board is ignored, the snake keeps circling
    Given the board is:
      """
      -----------
      -----------
      -----------
      -----------
      -----------
      --S---0----
      --t--------
      --t--------
      --T--------
      -----------
      -----------
      """
    When the snake receives a "/start" request
    And the snake receives a "/move" request
    Then the snake moves "up"

  Scenario: A snake across the ring makes us cut inward
    Given the board is:
      """
      -----------
      -----------
      -----------
      -----------
      -VvvvU-----
      --S--------
      --t--------
      --t--------
      --T--------
      -----------
      -----------
      """
    When the snake receives a "/start" request
    And the snake receives a "/move" request
    Then the snake moves "right"

  Scenario: Off the ring, the snake heads for it
    Given the board is:
      """
      -----------
      -----------
      -----------
      -----------
      -----------
      S----------
      t----------
      t----------
      T----------
      -----------
      -----------
      """
    When the snake receives a "/start" request
    And the snake receives a "/move" request
    Then the snake moves "right"
