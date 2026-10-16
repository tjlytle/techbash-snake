Feature: A tail square is free next turn
  In order to keep moves the rules allow
  As the TechBash snake
  I treat a tail square as empty, because the tail moves off it (a snake that just ate is a unit test: a board cannot draw a stacked tail)
  These boards leave the tail square as the only way out, so the move proves the rule whatever strategy is playing

  Scenario: Another snake's tail is the only way out of the corner
    Given the board is:
      """
      -----------
      -----------
      -----------
      -----------
      -----------
      -----------
      U----------
      v----------
      v----------
      V----------
      SttT-------
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

  Scenario: Our own tail closes a tight loop in the corner
    Given the board is:
      """
      -----------
      -----------
      -----------
      -----------
      -----------
      -----------
      -----------
      -----------
      -----------
      Tt---------
      St---------
      """
    When the snake receives a "/start" request
    And the snake receives a "/move" request
    Then the snake moves "up"
