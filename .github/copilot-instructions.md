# Conventions for PHP

- Any new file should contain the license header and has a single blank line after the opening PHP tag.
- The variable name should be in snake_case.
- We apply the PSR-4 coding standard.
- in_array() should be used with true as the third parameter.
- Only booleans should be used in if statements, not integers or strings.
- Use strict comparison (===) instead of loose comparison (==).
- Avoid code duplication in both if and else statements.
- Do not use empty() 

# Convention for the Application

- In Requests, if a user is provided by the query it is placed in the $this->user2.
- In Requests, $this->user is reserved for the user making the request.
- The Resource classes should extends from Spatie Data instead of JsonResource.
- We do not use blade view, we use Vue3 instead.

# Conventions for Vue3

- Use Typescript in composition API for Vue3. Use PrimeVue for UI components.
- Do not use await async calls in Vue3, use .then() instead.
- Do not use const function = () => {}, use function functionName() {} instead.