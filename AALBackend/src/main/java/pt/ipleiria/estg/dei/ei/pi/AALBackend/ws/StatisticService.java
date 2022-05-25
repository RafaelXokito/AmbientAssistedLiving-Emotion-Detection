package pt.ipleiria.estg.dei.ei.pi.AALBackend.ws;


import com.sun.tools.javac.util.Pair;
import pt.ipleiria.estg.dei.ei.pi.AALBackend.dtos.NotificationDTO;
import pt.ipleiria.estg.dei.ei.pi.AALBackend.dtos.StatisticDTO;
import pt.ipleiria.estg.dei.ei.pi.AALBackend.ejbs.IterationBean;
import pt.ipleiria.estg.dei.ei.pi.AALBackend.ejbs.NotificationBean;
import pt.ipleiria.estg.dei.ei.pi.AALBackend.ejbs.PersonBean;
import pt.ipleiria.estg.dei.ei.pi.AALBackend.entities.Iteration;
import pt.ipleiria.estg.dei.ei.pi.AALBackend.entities.Notification;

import javax.annotation.security.RolesAllowed;
import javax.ejb.EJB;
import javax.ws.rs.*;
import javax.ws.rs.core.Context;
import javax.ws.rs.core.MediaType;
import javax.ws.rs.core.Response;
import javax.ws.rs.core.SecurityContext;
import java.util.ArrayList;
import java.util.List;
import java.util.stream.Collectors;

@Path("statistics")
@Produces({MediaType.APPLICATION_JSON})
@Consumes({MediaType.APPLICATION_JSON})
@RolesAllowed({"Client", "Administrator"})
public class StatisticService {


    @EJB
    private IterationBean iterationBean;
    @EJB
    private NotificationBean notificationBean;
    @EJB
    private PersonBean personBean;
    @Context
    private SecurityContext securityContext;

    @GET
    @Path("/")
    public Response getAllNotificationsWS(@HeaderParam("Authorization") String auth) throws Exception {
        String clientEmail = personBean.getPersonByAuthToken(auth).getEmail();

        List<StatisticDTO> statistics = new ArrayList<>();
        if (securityContext.isUserInRole("Client")) {
            List<Notification> notifications = notificationBean.getAllNotificationsByClient(clientEmail, "false");
            if(notifications.size() > 0){
                statistics.add(toDTO("Total of notifications", String.valueOf(notifications.size())));
            }else{
                statistics.add(toDTO("Total of notifications", "No notifications"));
            }
            List<Pair<String, Long>> listPair = notificationBean.getEmotionsWithMostNotificationsByClient(clientEmail);
            if(listPair != null){
                statistics.add(toDTOExtended("Emotion with the most notifications", listPair.get(0).fst, String.valueOf(listPair.get(0).snd)));
            }else{
                statistics.add(toDTO("Emotion with the most notifications", "No notifications"));
            }

            Iteration iteration = iterationBean.getLastIterationByClient(clientEmail);
            if(iteration != null){
                statistics.add(toDTO("Last Iteration Time", String.valueOf(iteration.getCreated_at().getTime())));
            }else{
                statistics.add(toDTO("Last Iteration Time", "No iterations"));
            }
            List<Pair<String, Long>> listPair2 = notificationBean.getEmotionWithTheLeastNotificationsConfiguredByClient(clientEmail);
            if(listPair2 != null){
                statistics.add(toDTOExtended("Emotion with the least notifications configured", listPair2.get(0).fst, String.valueOf(listPair2.get(0).snd)));
            }else{
                statistics.add(toDTO("Emotion with the least notifications configured", "No notifications"));
            }

            return Response.status(Response.Status.OK)
                    .entity(statistics)
                    .build();
        }
        List<Notification> notifications = notificationBean.getAllNotifications( "false");
        if(notifications.size() > 0){
            statistics.add(toDTO("Total of notifications",String.valueOf(notifications.size())));
        }else{
            statistics.add(toDTO("Total of notifications","No notifications"));
        }

        List<Pair<String, Long>> listPair = notificationBean.getEmotionsWithMostNotifications();
        if(listPair != null){
            statistics.add(toDTOExtended("Emotion with the most notifications", listPair.get(0).fst, String.valueOf(listPair.get(0).snd)));
        }else{
            statistics.add(toDTO("Emotion with the most notifications", "No notifications"));
        }
        Iteration iteration = iterationBean.getLastIteration();
        if(iteration != null){
            statistics.add(toDTO("Last Iteration Time", String.valueOf(iteration.getCreated_at().getTime())));
        }else{
            statistics.add(toDTO("Last Iteration Time", "No iterations"));
        }
        List<Pair<String, Long>> listPair2 = notificationBean.getEmotionWithTheLeastNotificationsConfigured();
        if(listPair2 != null){
            statistics.add(toDTOExtended("Emotion with the least notifications configured", listPair2.get(0).fst, String.valueOf(listPair2.get(0).snd)));
        }else{
            statistics.add(toDTO("Emotion with the least notifications configured", "No notifications"));
        }
        return Response.status(Response.Status.OK)
                .entity(statistics)
                .build();
    }

    private StatisticDTO toDTOExtended(String name, String value, String subValue){
        return new StatisticDTO(name, value, subValue);
    }

    private StatisticDTO toDTO(String name, String value){
        return new StatisticDTO(name, value);
    }
}
