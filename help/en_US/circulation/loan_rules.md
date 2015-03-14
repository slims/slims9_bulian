####Loan Rules

This is a facility to define lending rules based on: 
- Member Type, 
- Collection Type, and 
- GMD. 

The rules set out in this facility are: 
- Limit number of loan items (Loan Limit), 
- Period of the loan (Loan Period), 
- Limit loan extensions (Reborrow Limit), 
- Penalty per day overdue (Fine Each Day), and 
- Overdue tolerance (Overdue Grace Period).

An example of defining Loan Rules:

1. In the library you have 3 types of collections: books, audiovisual (AV) and theses.
2. One type of membership your library has is : Student loans, with a total allowance of 2 items, namely: one item from the book collection and one more from the AV collection.
3. For that you would need to create the membership type: "Students" , with total borrowing from two collections.
4. So, in Loan Rules this must be defined:
	- Member type "Student", borrowing allowance for collection="Book" is 1.
	- Member type "Student", borrowing allowance for collection="AV" is 1.
	- Member type "Student", borrowing allowance for collection="Thesis" is 0.

Everything must be defined, otherwise it can be exceeded.
