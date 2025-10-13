####Loan Rules

This is a facility to define lending rules based on: 
- **Member type**, 
- **Collection type**, and 
- **GMD**. 

The rules set out in this facility are: 
- The limit to the number of loan items (**Loan limit**), 
- The period of the loan (**Loan period**), 
- The limit to loan extensions (**Reborrow limit**), 
- The penalty per day for an overdue item (**Fine each day**), and 
- Overdue tolerance (**Overdue grace period**).

An example of defining Loan Rules:

1. In the library you have 3 types of resources: books, audiovisual (AV) and theses.
2. One type of membership policy your library has is : <u>Student loans</u>, with a total allowance of 2 items, consisting of : one item from the book collection and one more from the AV collection.
3. For that you would need to create the membership type: "Student" , with total borrowing from two collections.
4. So, in *Loan Rules* these three rules must be defined:
	- Member type = "Student", Collection type ="Book" , loan limit = 1.
	- Member type "Student", Collection type ="AV",  loan limit = 1.
	- Member type "Student", Collection type ="Theses",  loan limit = 0.

Everything must be defined, otherwise limits can be exceeded.
