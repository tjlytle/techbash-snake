Feature: A tail square is free next turn
  In order to keep moves the rules allow
  As the TechBash snake
  I treat a tail square as empty, because the tail moves off it (a snake that just ate is a unit test: a board cannot draw a stacked tail)

  Scenario: Another snake's tail ahead on the ring does not stop the ring
    Given the board is:
      """
      -----------
      -----------
      -----------
      -----------
      --VvvU-----
      --S--------
      --t--------
      --t--------
      --T--------
      -----------
      -----------
      """
    When the snake receives a "/start" request
    And the snake receives a "/move" request
    Then the snake moves "up"

  Scenario: Our own tail is the only way out (game 8785e30c turn 32)
    Given the board is:
      """
      -----------
      --------tt-
      --------ST-
      ------vU---
      --0---vvv--
      --------V--
      -----------
      -----------
      -----------
      ---------0-
      -----------
      """
    When the snake receives a "/start" request
    And the snake receives a "/move" request
    Then the snake moves "right"

  Scenario: Our own tail ahead on the ring, in a tight loop
    Given the board is:
      """
      -----------
      -----------
      -----------
      -----------
      --Tt-------
      --St-------
      -----------
      -----------
      -----------
      -----------
      -----------
      """
    When the snake receives a "/start" request
    And the snake receives a "/move" request
    Then the snake moves "up"
