# Moodle Webservices for Cohort Enrolment.

* [What is this plugin and why was it developed?](#what-is-this-plugin-and-why-was-it-developed?)
* [What can this plugin do?](#what-can-this-plugin-do?)
* [How do I use this plugin?](#how-do-i-use-this-plugin?)
* [Examples JSON responses](#example-json-responses)

What is this plugin and why was it developed?
---------------------------------------------

This is a local plugin with webservice functions.

It was developed for <a href="https://www.twoa.ac.nz" target="_blank" alt="Link to Te Wānanga O Aotearoa website" title="Link to Te Wānanga O Aotearoa website">Te Wānanga O Aotearoa</a>
to streamline the withdrawal of tauira (student, learner) from courses. 


What can this plugin do?
-------------------------

This local plugin will remove a person from a cohort. First checking that activities or other 
grade items associated with a grade category considered gradeable have not been graded.
A grade category is considered gradeable if it has an idnumber matching a specific pattern. 
Activities or grade items may be either within the grade category or included as part of a grade calculation on that category.

How do I use this plugin?
-------------------------

This plugin can be used in accordance with the official 
<a href="https://docs.moodle.org/en/Using_web_services" target="_blank">Moodle documentation</a>.

In addition to required parameters, 
a Moodle user id and a cohort idnumber must be included. The cohort idnumber will map to the SMS class id.

Example JSON responses.
-----------------------
{"success":true,"comment":"Suspended from 1 courses, unenrolled from 0 courses."}
{"success":false,"comment":"Cohort not found."}
{"success":false,"comment":"The student was not found in this cohort.'}
